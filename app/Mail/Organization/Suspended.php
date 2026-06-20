<?php

declare(strict_types=1);

namespace App\Mail\Organization;

use App\Models\Organization;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class Suspended extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Organization $organization) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('mail/organization/suspended.subject'),
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
