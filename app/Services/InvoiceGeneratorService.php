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

    /**
     * Generate invoices for all active subscriptions.
     * Returns detailed report including last run info.
     */
    public function generateAll(): array
    {
        $lastState = $this->loadState();
        $startTime = now();

        $subscriptions = Subscription::with(['client', 'plan'])
            ->where('status', 'active')
            ->get();

        $created      = 0;
        $skipped      = 0;
        $noInvoice    = 0; // active subs with no invoices at all
        $errors       = [];
        $details      = [];

        foreach ($subscriptions as $subscription) {
            try {
                $result = $this->generateForSubscription($subscription);

                $hasAnyInvoice = Invoice::where('client_id', $subscription->client_id)
                    ->where('invoice_data_type', 'subscription')
                    ->where('invoice_data_id', $subscription->id)
                    ->exists();

                if (!$hasAnyInvoice) $noInvoice++;

                $details[] = [
                    'subscription_id' => $subscription->id,
                    'client'          => $subscription->client?->name ?? 'Unknown',
                    'plan'            => $subscription->plan?->name ?? 'Unknown',
                    'start_date'      => $subscription->start_date,
                    'billing_date'    => $subscription->billing_date,
                    'result'          => $result,
                ];

                if ($result === 'created') $created++;
                if ($result === 'skipped') $skipped++;
            } catch (\Exception $e) {
                $errors[] = [
                    'subscription_id' => $subscription->id,
                    'client'          => $subscription->client?->name ?? 'Unknown',
                    'error'           => $e->getMessage(),
                ];
                $details[] = [
                    'subscription_id' => $subscription->id,
                    'client'          => $subscription->client?->name ?? 'Unknown',
                    'plan'            => $subscription->plan?->name ?? 'Unknown',
                    'result'          => 'error: ' . $e->getMessage(),
                ];
            }
        }

        // Save state
        $state = [
            'last_run'           => $startTime->toISOString(),
            'last_run_human'     => $startTime->toDateTimeString(),
            'total_active'       => $subscriptions->count(),
            'last_created'       => $created,
            'last_skipped'       => $skipped,
            'last_no_invoice'    => $noInvoice,
        ];
        $this->saveState($state);

        return [
            'created'      => $created,
            'skipped'      => $skipped,
            'no_invoice'   => $noInvoice,
            'total'        => $subscriptions->count(),
            'errors'       => $errors,
            'details'      => $details,
            'last_run'     => $lastState['last_run_human'] ?? 'Never',
            'current_run'  => $startTime->toDateTimeString(),
        ];
    }

    /**
     * Generate invoice for a single subscription.
     * Logic: generate an invoice for each 30-day period since start/billing date up to today.
     */
    public function generateForSubscription(Subscription $subscription): string
    {
        if (!$subscription->client || !$subscription->plan) {
            return 'skipped_no_client_or_plan';
        }

        // Anchor = billing_date if set, otherwise start_date
        $anchor = Carbon::parse(
            $subscription->billing_date ?? $subscription->start_date
        )->startOfDay();

        $today  = Carbon::today();
        $generated = 0;

        // Walk forward from anchor date in 30-day increments
        // Generate an invoice for each period that has already started
        $periodStart = $anchor->copy();

        // Safety limit — max 24 periods (2 years)
        $maxIterations = 24;
        $iterations    = 0;

        while ($periodStart->lte($today) && $iterations < $maxIterations) {
            $iterations++;
            $dueDate = $periodStart->copy()->addDays(30);

            // Check if invoice already exists for this exact period
            $exists = Invoice::where('client_id', $subscription->client_id)
                ->where('invoice_data_type', 'subscription')
                ->where('invoice_data_id', $subscription->id)
                ->whereDate('due_date', $dueDate->toDateString())
                ->exists();

            if (!$exists) {
                $invoiceNo = 'INV-' . strtoupper(Str::random(4)) . '-' . $periodStart->format('Ymd');

                Invoice::create([
                    'client_id'         => $subscription->client_id,
                    'invoice_no'        => $invoiceNo,
                    'amount'            => $subscription->plan->price,
                    'due_date'          => $dueDate->toDateString(),
                    'status'            => $dueDate->lt($today) ? 'overdue' : 'unpaid',
                    'invoice_data_type' => 'subscription',
                    'invoice_data_id'   => $subscription->id,
                    'created_by'        => auth()->id() ?? null,
                ]);

                $generated++;
            }

            // Move to next period
            $periodStart->addDays(30);
        }

        return $generated > 0 ? "created:{$generated}" : 'skipped';
    }

    // ── State persistence ─────────────────────────────────────────────────
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