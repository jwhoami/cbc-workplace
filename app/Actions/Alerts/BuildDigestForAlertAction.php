<?php

declare(strict_types=1);

namespace App\Actions\Alerts;

use App\Enums\DispatchDecision;
use App\Enums\PublicEventKind;
use App\Mail\Member\JobAlertDigest;
use App\Models\JobAlert;
use App\Models\JobAlertDispatchLog;
use App\Models\PublicEvent;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class BuildDigestForAlertAction
{
    use AsAction;

    public function handle(JobAlert $alert, Carbon $windowStart, Carbon $windowEnd, string $windowKey): DispatchDecision
    {
        $alert->loadMissing('member');

        $matches = ResolveMatchingOffersAction::run($alert, $windowStart, $windowEnd);

        if ($matches->isEmpty()) {
            return $this->record($alert, $windowKey, DispatchDecision::SuppressedNoMatch, null,
                PublicEventKind::AlertEmailSuppressedNoMatch);
        }

        if ($alert->member?->isEmailInvalid()) {
            return $this->record($alert, $windowKey, DispatchDecision::SuppressedInvalidRecipient, null,
                PublicEventKind::AlertEmailSuppressedInvalidRecipient, ['reason' => 'invalid_recipient']);
        }

        $correlationId = (string) Str::uuid();

        try {
            $log = JobAlertDispatchLog::create([
                'job_alert_id' => $alert->id,
                'window_key' => $windowKey,
                'decision' => DispatchDecision::Sent->value,
                'matched_offer_ids' => $matches->pluck('id')->all(),
                'correlation_id' => $correlationId,
                'dispatched_at' => now(),
            ]);
        } catch (UniqueConstraintViolationException) {
            // Lost a race; another worker already wrote the (alert, window)
            // row + queued the mail + emitted the event. This invocation
            // must NOT increment the dispatcher's `sent` counter — return a
            // distinct decision so the caller can bucket it as
            // `dedup_absorbed` (spec 008 T075 Finding 2 follow-up).
            return DispatchDecision::AlreadySent;
        }

        Mail::to($alert->member)->queue(new JobAlertDigest($alert, $matches, $alert->frequency));

        PublicEvent::create([
            'kind' => PublicEventKind::AlertEmailSent,
            'correlation_id' => $correlationId,
            'occurred_at' => now(),
            'path' => '/alerts/dispatch',
            'visitor_variant' => 'system',
            'payload' => [
                'member_id' => $alert->member_id,
                'alert_id' => $alert->id,
                'dispatch_log_id' => $log->id,
                'window_key' => $windowKey,
                'offer_count' => $matches->count(),
                'frequency' => $alert->frequency->value,
            ],
        ]);

        return DispatchDecision::Sent;
    }

    private function record(
        JobAlert $alert,
        string $windowKey,
        DispatchDecision $decision,
        ?array $offers,
        PublicEventKind $eventKind,
        array $extraPayload = [],
    ): DispatchDecision {
        $correlationId = (string) Str::uuid();

        try {
            JobAlertDispatchLog::create([
                'job_alert_id' => $alert->id,
                'window_key' => $windowKey,
                'decision' => $decision->value,
                'matched_offer_ids' => $offers,
                'correlation_id' => $correlationId,
                'dispatched_at' => now(),
            ]);
        } catch (UniqueConstraintViolationException) {
            return $decision;
        }

        PublicEvent::create([
            'kind' => $eventKind,
            'correlation_id' => $correlationId,
            'occurred_at' => now(),
            'path' => '/alerts/dispatch',
            'visitor_variant' => 'system',
            'payload' => array_merge([
                'member_id' => $alert->member_id,
                'alert_id' => $alert->id,
                'window_key' => $windowKey,
                'frequency' => $alert->frequency->value,
            ], $extraPayload),
        ]);

        return $decision;
    }
}
