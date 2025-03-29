<?php

namespace App\Jobs;

use App\Models\Subscription;
use App\Models\CronLog;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProcessSubscriptionBilling implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $today = Carbon::today();
        Log::info("Cron job started: Processing subscriptions.");

        try {
            $subscriptions = Subscription::where('status', 'active')->get();
            Log::info("Number of active subscriptions: " . $subscriptions->count());

            foreach ($subscriptions as $subscription) {
                $billingDate = Carbon::parse($subscription->billing_date);

                if ($billingDate->lessThan($today)) {
                    $newBillingDate = $billingDate->addMonth();
                    $subscription->update(['billing_date' => $newBillingDate]);
                    Log::info("Updated billing date for Subscription ID: {$subscription->id} to {$newBillingDate->toDateString()}");
                }

                if ($billingDate->isSameDay($today)) {
                    try {
                        $this->generateInvoice($subscription->id);
                        Log::info("Invoice generated for Subscription ID: {$subscription->id}");
                    } catch (\Exception $e) {
                        Log::error("Failed to generate invoice for Subscription ID: {$subscription->id}, Error: " . $e->getMessage());
                    }
                }
            }

            CronLog::create(['job_name' => 'Subscription Billing', 'status' => 'success']);
            Log::info("Cron job finished.");
        } catch (\Exception $e) {
            CronLog::create([
                'job_name' => 'Subscription Billing',
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
            Log::error("Cron job failed: " . $e->getMessage());

            // Re-throw exception to allow Laravel's retry mechanism to handle it
            throw $e;
        }
    }

    private function generateInvoice($subscriptionId)
    {
        $subscription = Subscription::with(['client', 'plan'])->findOrFail($subscriptionId);
        $amount = $subscription->plan->price;
        $invoiceNo = 'INV-' . $subscription->id . '-' . now()->format('YmdHis');

        Invoice::create([
            'client_id'  => $subscription->client->id,
            'invoice_no' => $invoiceNo,
            'amount'     => $amount,
            'due_date'   => now()->addDays(30)->toDateString(),
            'status'     => 'unpaid',
        ]);

        Log::info("Invoice created for Subscription ID: {$subscriptionId}");
    }
}
