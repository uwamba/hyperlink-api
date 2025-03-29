<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ProcessSubscriptionBilling;

class ProcessSubscriptionBillingCommand extends Command
{
    protected $signature = 'subscriptions:process';
    protected $description = 'Process subscription billing and generate invoices.';

    public function handle()
    {
        dispatch(new ProcessSubscriptionBilling());
        $this->info('Subscription billing job dispatched.');
    }
}
