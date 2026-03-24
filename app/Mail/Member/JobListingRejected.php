<?php

namespace App\Mail\Member;

use App\Models\JobListing;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class JobListingRejected extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public JobListing $jobListing)
    {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Oferta de Empleo Rechazada',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.member.job-listing-rejected',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
