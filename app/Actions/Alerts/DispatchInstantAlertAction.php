<?php

declare(strict_types=1);

namespace App\Actions\Alerts;

use App\Enums\DispatchDecision;
use App\Enums\PublicEventKind;
use App\Helpers\Util;
use App\Mail\Member\JobAlertInstantBatch;
use App\Models\JobAlert;
use App\Models\JobAlertDispatchLog;
use App\Models\PublicEvent;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\AsJob;

class DispatchInstantAlertAction
{
    use AsAction;
    use AsJob;

    public function handle(int $alertId, string $windowKey): DispatchDecision
    {
        $window = Cache::pull('alert-window:'.$alertId);

        if ($window === null) {
            $this->emitSuppression($alertId, $windowKey, ['reason' => 'window_empty']);

            return DispatchDecision::SuppressedNoMatch;
        }

        $alert = JobAlert::query()->with('member')->find($alertId);

        if ($alert === null) {
            $this->emitSuppression($alertId, $windowKey, ['reason' => 'alert_deleted']);

            return DispatchDecision::SuppressedNoMatch;
        }

        if (! $alert->active || ! ($alert->member?->is_active ?? false)) {
            $this->emitSuppression($alertId, $windowKey, [
                'reason' => $alert->active ? 'member_inactive' : 'alert_disabled',
            ]);
            $this->logSummary($alert->id, $windowKey, 'suppressed');

            return DispatchDecision::SuppressedNoMatch;
        }

        // FR-022: the offer that triggered coalescence has `published_at` set
        // by JobListingApproval::approve() BEFORE the listener fires, so its
        // timestamp is earlier than `opens_at`. Without a lookback grace the
        // re-validation window `[opens_at, now()]` excludes the very offer that
        // opened the window. 60s is comfortably more than worst-case queue lag
        // between approval and listener wakeup while still satisfying the
        // contract's "drop withdrawn offers" intent (any offer rejected within
        // 60s of approval is exceptional and acceptable to include).
        $opensAt = Carbon::parse($window['opens_at'])->copy()->subSeconds(60);
        $matches = ResolveMatchingOffersAction::run($alert, $opensAt, now());

        if ($matches->isEmpty()) {
            $this->emitSuppression($alert->id, $windowKey, ['reason' => 'no_match']);
            $this->logSummary($alert->id, $windowKey, 'suppressed_no_match');

            return DispatchDecision::SuppressedNoMatch;
        }

        if ($alert->member->isEmailInvalid()) {
            $this->emitSuppressionWith(
                $alert->id,
                $windowKey,
                PublicEventKind::AlertEmailSuppressedInvalidRecipient,
                ['reason' => 'invalid_recipient']
            );
            $this->logSummary($alert->id, $windowKey, 'suppressed_invalid');

            return DispatchDecision::SuppressedInvalidRecipient;
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
            return DispatchDecision::Sent;
        }

        Mail::to($alert->member)->queue(new JobAlertInstantBatch($alert, $matches));

        PublicEvent::create([
            'kind' => PublicEventKind::AlertEmailSent,
            'correlation_id' => $correlationId,
            'occurred_at' => now(),
            'path' => '/alerts/instant',
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

        $this->logSummary($alert->id, $windowKey, 'sent', $matches->count());

        return DispatchDecision::Sent;
    }

    private function emitSuppression(int $alertId, string $windowKey, array $extra = []): void
    {
        $this->emitSuppressionWith($alertId, $windowKey, PublicEventKind::AlertEmailSuppressedNoMatch, $extra);
    }

    private function emitSuppressionWith(int $alertId, string $windowKey, PublicEventKind $kind, array $extra): void
    {
        PublicEvent::create([
            'kind' => $kind,
            'correlation_id' => (string) Str::uuid(),
            'occurred_at' => now(),
            'path' => '/alerts/instant',
            'visitor_variant' => 'system',
            'payload' => array_merge([
                'alert_id' => $alertId,
                'window_key' => $windowKey,
            ], $extra),
        ]);
    }

    private function logSummary(int $alertId, string $windowKey, string $outcome, int $offerCount = 0): void
    {
        Util::getActivityLog('job-alert.dispatch.instant')
            ->withProperties([
                'summary' => [
                    'alert_id' => $alertId,
                    'window_key' => $windowKey,
                    'outcome' => $outcome,
                    'offer_count' => $offerCount,
                ],
            ])
            ->log('Despacho instantáneo de alerta');
    }
}
