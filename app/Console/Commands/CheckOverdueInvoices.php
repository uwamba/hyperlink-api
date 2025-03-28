<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use Carbon\Carbon;

class CheckOverdueInvoices extends Command
{
    protected $signature = 'invoices:check-overdue';
    protected $description = 'Check overdue invoices and update their status';

    public function handle()
    {
        $today = Carbon::today();

        echo 'cron tab runnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnn';

        $overdueInvoices = Invoice::where('status', 'unpaid')
            ->where('due_date', '<', $today)
            ->get();

        foreach ($overdueInvoices as $invoice) {
            $invoice->update(['status' => 'overdue']);
        }

        $this->info(count($overdueInvoices) . ' invoices marked as overdue.');
    }
}
