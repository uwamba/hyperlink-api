<?php

namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
class SupportFeedbackMail extends Mailable
{
    use Queueable, SerializesModels;

    public $ticket;
    public $feedback;

    public function __construct($ticket, $feedback)
    {
        $this->ticket = $ticket;
        $this->feedback = $feedback;
    }

    public function build()
    {
        return $this->subject('Feedback on Your Support Ticket')
                    ->view('emails.support_feedback')
                    ->with([
                        'ticket' => $this->ticket,
                        'feedback' => $this->feedback,
                    ]);
    }
}
