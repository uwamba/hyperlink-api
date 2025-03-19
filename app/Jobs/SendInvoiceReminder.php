<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

class SendInvoiceReminder implements ShouldQueue
{
    use Dispatchable, Queueable;

    protected $invoice;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function handle()
    {
        $client = Client::find($this->invoice->client_id);

        if ($this->invoice->status === 'unpaid' && $this->invoice->due_date->isToday()) {
            // Send reminder email to client
            Mail::to($client->email)->send(new \App\Mail\InvoiceReminder($this->invoice));
        }
    }
}
