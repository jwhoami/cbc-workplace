<?php

namespace App\Mail\Admin;

use App\Models\JobListing;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class JobListingSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public JobListing $jobListing)
    {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nueva Oferta de Empleo Pendiente de Aprobación',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.admin.job-listing-submitted',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
