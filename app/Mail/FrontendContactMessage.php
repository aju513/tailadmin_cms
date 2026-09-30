<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class FrontendContactMessage extends Mailable
{
    public function __construct(public readonly array $details) {}

    public function envelope(): Envelope
    {
        return new Envelope(replyTo: [new Address($this->details['mail'], $this->details['name'])], subject: 'Website contact message');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.frontend-contact-message');
    }
}
