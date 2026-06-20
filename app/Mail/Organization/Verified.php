<?php

namespace App\Mail\Organization;

use App\Models\Organization;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class Verified extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Organization $organization)
    {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Su organización ha sido verificada'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.organization.verified',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
