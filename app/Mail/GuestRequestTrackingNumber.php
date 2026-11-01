<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GuestRequestTrackingNumber extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $requesterName,
        public string $trackingCode,
        public string $lastName
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your UA facility request tracking number');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.guest-request-tracking-number');
    }
}