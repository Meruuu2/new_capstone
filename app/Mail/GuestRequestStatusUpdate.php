<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GuestRequestStatusUpdate extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $requesterName,
        public string $trackingCode,
        public string $lastName,
        public string $event,
        public array $details = []
    ) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->event) {
            'approved' => 'Your UA facility request was approved and booked',
            'booked' => 'Your UA facility request is booked',
            'disapproved' => 'Update on your UA facility request',
            'payment_verified' => 'Your UA facility payment was verified',
            'rescheduled' => 'Your UA facility booking was rescheduled',
            'cancelled' => 'Your UA facility booking was cancelled',
            default => 'Update on your UA facility request',
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.guest-request-status-update');
    }
}