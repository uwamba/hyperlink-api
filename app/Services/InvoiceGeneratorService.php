<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\Invoice;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class InvoiceGeneratorService
{
    private string $stateFile;

    public function __construct()
    {
        $this->stateFile = storage_path('app/invoice_generator_state.json');
    }

    public function generateAll(): array
    {
        $lastState = $this->loadState();
        $startTime = now();

        $subscriptions = Subscription::with(['client', 'plan'])
            ->where('status', 'active')
            ->get();

        $created   = 0;
        $skipped   = 0;
        $noInvoice = 0;
        $errors    = [];
        $details   = [];

        foreach ($subscriptions as $subscription) {
            $hadInvoiceBefore = Invoice::where('invoice_data_type', 'subscription')
                ->where('invoice_data_id', $subscription->id)
                ->exists();

            if (!$hadInvoiceBefore) $noInvoice++;

            try {
                [$result, $count, $reason] = $this->generateForSubscription($subscription);

                if ($result === 'created') {
                    $created += $count;
                } else {
                    $skipped++;
                }

                $details[] = [
                    'subscription_id' => $subscription->id,
                    'client'          => $subscription->client?->name ?? 'Unknown',
                    'plan'            => $subscription->plan?->name ?? 'Unknown',
                    'start_date'      => $subscription->start_date,
                    'billing_date'    => $subscription->billing_date,
                    'had_invoice'     => $hadInvoiceBefore,
                    'result'          => $result === 'created' ? "created:{$count}" : "skipped ({$reason})",
                ];

            } catch (\Exception $e) {
                Log::error('InvoiceGenerator error', [
                    'subscription_id' => $subscription->id,
                    'error'           => $e->getMessage(),
                ]);
                $errors[]  = [
                    'subscription_id' => $subscription->id,
                    'client'          => $subscription->client?->name ?? 'Unknown',
                    'error'           => $e->getMessage(),
                ];
                $details[] = [
                    'subscription_id' => $subscription->id,
                    'client'          => $subscription->client?->name ?? 'Unknown',
                    'plan'            => $subscription->plan?->name ?? 'Unknown',
                    'start_date'      => $subscription->start_date,
                    'billing_date'    => $subscription->billing_date,
                    'had_invoice'     => $hadInvoiceBefore,
                    'result'          => 'error: ' . $e->getMessage(),
                ];
            }
        }

        $this->saveState([
            'last_run'        => $startTime->toISOString(),
            'last_run_human'  => $startTime->toDateTimeString(),
            'total_active'    => $subscriptions->count(),
            'last_created'    => $created,
            'last_skipped'    => $skipped,
            'last_no_invoice' => $noInvoice,
        ]);

        return [
            'created'     => $created,
            'skipped'     => $skipped,
            'no_invoice'  => $noInvoice,
            'total'       => $subscriptions->count(),
            'errors'      => $errors,
            'details'     => $details,
            'last_run'    => $lastState['last_run_human'] ?? 'Never',
            'current_run' => $startTime->toDateTimeString(),
        ];
    }

    /**
     * Returns [status, count, reason]
     */
    public function generateForSubscription(Subscription $subscription): array
    {
        if (!$subscription->client) {
            return ['skipped', 0, 'no client'];
        }
        if (!$subscription->plan) {
            return ['skipped', 0, 'no plan'];
        }
        if (!$subscription->plan->price) {
            return ['skipped', 0, 'plan has no price'];
        }

        // Use billing_date if set, otherwise start_date
        $anchorDate  = $subscription->billing_date ?? $subscription->start_date;
        if (!$anchorDate) {
            return ['skipped', 0, 'no start or billing date'];
        }

        $anchor      = Carbon::parse($anchorDate)->startOfDay();
        $today       = Carbon::today();
        $generated   = 0;
        $maxPeriods  = 36;
        $periodStart = $anchor->copy();

        // If anchor is in the future, still generate one upcoming invoice
        // If anchor is today or past, generate all due periods
        if ($anchor->gt($today)) {
            // Subscription starts in future — generate first upcoming invoice
            $dueDate = $anchor->copy()->addDays(30);
            $exists  = Invoice::where('invoice_data_type', 'subscription')
                ->where('invoice_data_id', $subscription->id)
                ->whereDate('due_date', $dueDate->toDateString())
                ->exists();

            if (!$exists) {
                $this->createInvoice($subscription, $anchor, $dueDate, $today);
                $generated++;
            }
            return $generated > 0
                ? ['created', $generated, '']
                : ['skipped', 0, 'future invoice already exists'];
        }

        // Walk forward from anchor in 30-day steps
        while ($periodStart->lte($today) && $maxPeriods-- > 0) {
            $dueDate = $periodStart->copy()->addDays(30);

            // Check for existing invoice for this period
            $exists = Invoice::where('invoice_data_type', 'subscription')
                ->where('invoice_data_id', $subscription->id)
                ->whereDate('due_date', $dueDate->toDateString())
                ->exists();

            if (!$exists) {
                $this->createInvoice($subscription, $periodStart, $dueDate, $today);
                $generated++;
            }

            $periodStart->addDays(30);
        }

        if ($generated > 0) {
            return ['created', $generated, ''];
        }

        // All periods already have invoices
        return ['skipped', 0, 'all periods already invoiced'];
    }

    private function createInvoice(
        Subscription $subscription,
        Carbon $periodStart,
        Carbon $dueDate,
        Carbon $today
    ): Invoice {
        return Invoice::create([
            'client_id'         => $subscription->client_id,
            'invoice_no'        => 'INV-' . strtoupper(Str::random(6)) . '-' . $periodStart->format('Ymd'),
            'amount'            => $subscription->plan->price,
            'due_date'          => $dueDate->toDateString(),
            'status'            => $dueDate->lt($today) ? 'overdue' : 'unpaid',
            'invoice_data_type' => 'subscription',
            'invoice_data_id'   => $subscription->id,
            'created_by'        => auth()->id() ?? null,
        ]);
    }

    private function loadState(): array
    {
        if (!file_exists($this->stateFile)) return [];
        return json_decode(file_get_contents($this->stateFile), true) ?? [];
    }

    private function saveState(array $state): void
    {
        file_put_contents($this->stateFile, json_encode($state, JSON_PRETTY_PRINT));
    }

    public function getLastState(): array
    {
        return $this->loadState();
    }
}