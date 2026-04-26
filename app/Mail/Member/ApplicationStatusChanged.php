<?php

namespace App\Mail\Member;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationStatusChanged extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Application $application,
        public ApplicationStatus $previous,
        public ApplicationStatus $current,
    ) {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('mail/application-status-changed.subject', [
                'status_label' => $this->current->getLabel(),
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.member.application-status-changed',
            with: [
                'application' => $this->application,
                'listing' => $this->application->jobListing,
                'organization' => $this->application->jobListing->organization,
                'candidateName' => $this->application->candidate_name_snapshot,
                'previous' => $this->previous,
                'current' => $this->current,
                'currentLabel' => $this->current->getLabel(),
                'bodyKey' => 'mail/application-status-changed.body.'.strtolower($this->current->name),
            ],
        );
    }
}
