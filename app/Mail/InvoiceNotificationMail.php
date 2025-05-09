<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade as PDF;
use App\Models\Invoice;

class InvoiceNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $invoice;
    public $pdf;

    public function __construct(Invoice $invoice, $pdf)
    {
        $this->invoice = $invoice;
        $this->pdf = $pdf;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invoice Notification',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoiceMailNotification',
            with: [
                'invoice' => $this->invoice,
            ],
        );
    }

    public function attachments(): array
    {
        return [
            [
                'data' => $this->pdf->output(),
                'name' => 'invoice_' . $this->invoice->invoice_no . '.pdf',
                'mime' => 'application/pdf',
            ],
        ];
    }
}
