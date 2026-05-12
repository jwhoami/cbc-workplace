<?php

declare(strict_types=1);

namespace App\Mail\Member;

use App\Enums\JobAlertFrequency;
use App\Helpers\Util;
use App\Models\JobAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use Throwable;

class JobAlertDigest extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public JobAlert $alert,
        public Collection $offers,
        public JobAlertFrequency $frequency,
    ) {
        //
    }

    public function envelope(): Envelope
    {
        $key = $this->frequency === JobAlertFrequency::Daily
            ? 'mail/job-alert.digest.daily.subject'
            : 'mail/job-alert.digest.weekly.subject';

        return new Envelope(
            subject: __($key, ['count' => $this->offers->count()]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.member.job-alert-digest',
            with: [
                'alert' => $this->alert,
                'offers' => $this->offers,
                'frequency' => $this->frequency,
                // Long-lived signed URL (no expiration) per FR-028a.
                'unsubscribeUrl' => URL::signedRoute(
                    'alerts.unsubscribe',
                    [
                        'member' => $this->alert->member_id,
                        'alert' => $this->alert->id,
                    ],
                ),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }

    public function failed(Throwable $exception): void
    {
        Util::getActivityLog('job-alert.dispatch.failed')
            ->withProperties([
                'alert_id' => $this->alert->id,
                'member_id' => $this->alert->member_id,
                'offer_ids' => $this->offers->pluck('id')->all(),
                'reason' => $exception->getMessage(),
            ])
            ->log('alert-mail-failed');
    }
}
