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
use App\Mail\InvoiceNotificationMail;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceOverdueMail;
use App\Mail\InvoiceReminderMail;

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



            // check overdue invoices and sent notifications

            $today = Carbon::today();
            $fiveDaysLater = $today->copy()->addDays(5);

            // 1. Invoices already marked as overdue
            $overdues = Invoice::where('status', 'overdue')->get();

            foreach ($overdues as $invoice) {
                if ($invoice->client && $invoice->client->email) {
                    Mail::to($invoice->client->email)->send(new InvoiceOverdueMail($invoice));
                    Log::info("✅ Overdue invoice email sent to: {$invoice->client->email} (Invoice: {$invoice->invoice_no})");
                }
            }

            // 2. Reminder for invoices due in 5 days
            $reminders = Invoice::where('status', '!=', 'paid')
                ->where('status', '!=', 'overdue')
                ->whereDate('due_date', $fiveDaysLater)
                ->get();

            foreach ($reminders as $invoice) {
                if ($invoice->client && $invoice->client->email) {
                    Mail::to($invoice->client->email)->send(new InvoiceReminderMail($invoice));
                    Log::info("📧 Reminder email sent to: {$invoice->client->email} (Invoice: {$invoice->invoice_no})");
                }
            }

      

            CronLog::create(['job_name' => 'Subscription Billing', 'status' => 'success']);
            Log::info("Cron job finished without error.");
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

    private function generateInvoice_old($subscriptionId)
    {
        $subscription = Subscription::with(['client', 'plan'])->findOrFail($subscriptionId);
        $amount = $subscription->plan->price;
        $invoiceNo = 'INV-' . $subscription->id . '-' . now()->format('YmdHis');

        Invoice::create([
            'client_id' => $subscription->client->id,
            'invoice_no' => $invoiceNo,
            'amount' => $amount,
            'due_date' => now()->addDays(30)->toDateString(),
            'status' => 'unpaid',
        ]);

        Log::info("Invoice created for Subscription ID: {$subscriptionId}");
    }




    private function generateInvoice($subscriptionId)
    {
        $subscription = Subscription::with(['client', 'plan'])->findOrFail($subscriptionId);
        $amount = $subscription->plan->price;
        $invoiceNo = 'INV-' . $subscription->id . '-' . now()->format('YmdHis');

        $invoice = Invoice::create([
            'client_id' => $subscription->client->id,
            'invoice_no' => $invoiceNo,
            'amount' => $amount,
            'due_date' => now()->addDays(30)->toDateString(),
            'status' => 'unpaid',
        ]);
        Log::info("Invoice created for Subscription ID: {$subscriptionId}");

        // Send email notification
        if ($subscription->client && $subscription->client->email) {
            Mail::to($subscription->client->email)->send(new InvoiceNotificationMail($invoice));
            Log::info("Invoice email sent to: " . $subscription->client->email);
        }
    }

}
