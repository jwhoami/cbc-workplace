<?php

namespace App\Mail\Organization;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Application $application)
    {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('mail/application-received.subject', [
                'listing_title' => $this->application->jobListing->title,
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.organization.application-received',
            with: [
                'application' => $this->application,
                'listing' => $this->application->jobListing,
                'organization' => $this->application->jobListing->organization,
                'candidateName' => $this->application->candidate_name_snapshot,
                'submittedAt' => $this->application->submitted_at,
            ],
        );
    }
}
