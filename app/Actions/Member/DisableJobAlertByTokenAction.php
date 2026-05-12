<?php

declare(strict_types=1);

namespace App\Actions\Member;

use App\Enums\PublicEventKind;
use App\Helpers\Util;
use App\Models\JobAlert;
use App\Models\Member;
use App\Models\PublicEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class DisableJobAlertByTokenAction
{
    use AsAction;

    public function handle(Member $member, JobAlert $alert): void
    {
        $wasAlreadyInactive = ! $alert->active;

        DB::transaction(function () use ($alert, $member, $wasAlreadyInactive) {
            if (! $wasAlreadyInactive) {
                $alert->active = false;
                $alert->save();
            }

            PublicEvent::create([
                'kind' => PublicEventKind::AlertUnsubscribedViaLink,
                'correlation_id' => (string) Str::uuid(),
                'occurred_at' => now(),
                'path' => '/alerts/unsubscribe',
                'visitor_variant' => 'anonymous',
                'payload' => [
                    'member_id' => $member->id,
                    'alert_id' => $alert->id,
                    'was_already_inactive' => $wasAlreadyInactive,
                ],
            ]);
        });

        if (! $wasAlreadyInactive) {
            $alert->addComment(__('models/job-alert.comments.unsubscribed_via_link'));
        }

        Util::getActivityLog('job-alert.unsubscribe-via-link')
            ->performedOn($alert)
            ->withProperties([
                'was_already_inactive' => $wasAlreadyInactive,
                'ip' => request()->ip(),
            ])
            ->log('Alerta deshabilitada desde enlace de desuscripción');
    }
}
