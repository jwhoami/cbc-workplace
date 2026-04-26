<?php

namespace App\Mail\Member;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Application $application)
    {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('mail/application-submitted.subject', [
                'listing_title' => $this->application->jobListing->title,
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.member.application-submitted',
            with: [
                'application' => $this->application,
                'listing' => $this->application->jobListing,
                'organization' => $this->application->jobListing->organization,
                'candidateName' => $this->application->candidate_name_snapshot,
            ],
        );
    }
}
