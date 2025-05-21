<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Invoice;

class InvoiceNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $invoice;
    public $pdf;

    public function __construct(Invoice $invoice, $pdf)
    {
        $this->invoice = $invoice;
        $this->pdf = $pdf;
    }

    public function build()
    {
        return $this->subject('Invoice Notification')
            ->view('emails.invoiceMailNotification')
            ->with([
                'invoice' => $this->invoice,
            ])
            ->attachData(
                $this->pdf->output(),
                'invoice_' . $this->invoice->invoice_no . '.pdf',
                [
                    'mime' => 'application/pdf',
                ]
            );
    }
}
