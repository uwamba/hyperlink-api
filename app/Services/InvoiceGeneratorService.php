<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\Invoice;
use Illuminate\Support\Str;
use Carbon\Carbon;

class InvoiceGeneratorService
{
    /**
     * Generate invoices for all active subscriptions.
     * Skips if an invoice already exists for the current billing period.
     * Returns a summary of what was created and what was skipped.
     */
    public function generateAll(): array
    {
        $subscriptions = Subscription::with(['client', 'plan'])
            ->where('status', 'active')
            ->get();

        $created  = 0;
        $skipped  = 0;
        $errors   = [];

        foreach ($subscriptions as $subscription) {
            try {
                $result = $this->generateForSubscription($subscription);
                if ($result === 'created') $created++;
                if ($result === 'skipped') $skipped++;
            } catch (\Exception $e) {
                $errors[] = "Subscription {$subscription->id}: {$e->getMessage()}";
            }
        }

        return [
            'created' => $created,
            'skipped' => $skipped,
            'errors'  => $errors,
            'total'   => $subscriptions->count(),
        ];
    }

    /**
     * Generate invoice for a single subscription.
     * Uses billing_date or start_date to determine the current billing period.
     */
    public function generateForSubscription(Subscription $subscription): string
    {
        if (!$subscription->client || !$subscription->plan) {
            return 'skipped';
        }

        // Determine billing anchor date
        $anchor    = Carbon::parse($subscription->billing_date ?? $subscription->start_date);
        $today     = Carbon::today();

        // Find the current period start — the most recent billing cycle date on or before today
        $periodStart = $anchor->copy();
        while ($periodStart->copy()->addDays(30)->lte($today)) {
            $periodStart->addDays(30);
        }

        // Period end is 30 days after period start
        $periodEnd = $periodStart->copy()->addDays(30);
        $dueDate   = $periodEnd->copy(); // due at end of period

        // Skip if invoice already exists for this period
        $exists = Invoice::where('client_id', $subscription->client_id)
            ->where('invoice_data_type', 'subscription')
            ->where('invoice_data_id', $subscription->id)
            ->whereDate('due_date', $dueDate->toDateString())
            ->exists();

        if ($exists) {
            return 'skipped';
        }

        // Generate unique invoice number
        $invoiceNo = 'INV-' . strtoupper(Str::random(4)) . '-' . date('Ymd');

        Invoice::create([
            'client_id'         => $subscription->client_id,
            'invoice_no'        => $invoiceNo,
            'amount'            => $subscription->plan->price,
            'due_date'          => $dueDate->toDateString(),
            'status'            => 'unpaid',
            'invoice_data_type' => 'subscription',
            'invoice_data_id'   => $subscription->id,
            'created_by'        => auth()->id() ?? null,
        ]);

        return 'created';
    }
}