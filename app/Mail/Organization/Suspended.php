<?php

namespace App\Mail\Organization;

use App\Models\Organization;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class Suspended extends Mailable
{
    use Queueable, SerializesModels;

    public string $reason;

    public function __construct(public Organization $organization, string $reason)
    {
        $this->reason = $reason;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Su organización ha sido suspendida'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.organization.suspended',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
