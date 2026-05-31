<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\Invoice;
use Illuminate\Support\Str;
use Carbon\Carbon;

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
            // Count subs without ANY invoice BEFORE generation
            $hadInvoiceBefore = Invoice::where('client_id', $subscription->client_id)
                ->where('invoice_data_type', 'subscription')
                ->where('invoice_data_id', $subscription->id)
                ->exists();

            if (!$hadInvoiceBefore) $noInvoice++;

            try {
                $result = $this->generateForSubscription($subscription);

                if (str_starts_with($result, 'created:')) {
                    $created += (int) explode(':', $result)[1];
                } elseif ($result === 'skipped') {
                    $skipped++;
                }

                $details[] = [
                    'subscription_id' => $subscription->id,
                    'client'          => $subscription->client?->name ?? 'Unknown',
                    'plan'            => $subscription->plan?->name ?? 'Unknown',
                    'start_date'      => $subscription->start_date,
                    'billing_date'    => $subscription->billing_date,
                    'had_invoice'     => $hadInvoiceBefore,
                    'result'          => $result,
                ];
            } catch (\Exception $e) {
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
     * Walk forward from anchor date in 30-day steps.
     * Generate an invoice for every period whose start <= today.
     * Skip if invoice with that due_date already exists.
     */
    public function generateForSubscription(Subscription $subscription): string
    {
        if (!$subscription->client || !$subscription->plan) {
            return 'skipped_no_client_or_plan';
        }

        $anchor      = Carbon::parse(
            $subscription->billing_date ?? $subscription->start_date
        )->startOfDay();

        $today       = Carbon::today();
        $generated   = 0;
        $maxPeriods  = 36; // safety cap — 3 years max
        $periodStart = $anchor->copy();

        while ($periodStart->lte($today) && $maxPeriods-- > 0) {
            $dueDate = $periodStart->copy()->addDays(30);

            $exists = Invoice::where('client_id', $subscription->client_id)
                ->where('invoice_data_type', 'subscription')
                ->where('invoice_data_id', $subscription->id)
                ->whereDate('due_date', $dueDate->toDateString())
                ->exists();

            if (!$exists) {
                Invoice::create([
                    'client_id'         => $subscription->client_id,
                    'invoice_no'        => 'INV-' . strtoupper(Str::random(6)) . '-' . $periodStart->format('Ymd'),
                    'amount'            => $subscription->plan->price,
                    'due_date'          => $dueDate->toDateString(),
                    // overdue if due date is in the past, unpaid if in future
                    'status'            => $dueDate->lt($today) ? 'overdue' : 'unpaid',
                    'invoice_data_type' => 'subscription',
                    'invoice_data_id'   => $subscription->id,
                    'created_by'        => auth()->id() ?? null,
                ]);
                $generated++;
            }

            $periodStart->addDays(30);
        }

        return $generated > 0 ? "created:{$generated}" : 'skipped';
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