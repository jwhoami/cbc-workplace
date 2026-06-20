<?php

declare(strict_types=1);

namespace App\Actions\Member;

use App\Enums\PublicEventKind;
use App\Models\JobAlert;
use App\Models\PublicEvent;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class ToggleJobAlertAction
{
    use AsAction;

    public function handle(JobAlert $alert): JobAlert
    {
        DB::transaction(function () use ($alert) {
            $alert->active = ! $alert->active;
            $alert->save();

            PublicEvent::create([
                'kind' => PublicEventKind::AlertToggled,
                'correlation_id' => (string) Str::uuid(),
                'occurred_at' => now(),
                'path' => '/member/job-alerts',
                'visitor_variant' => 'member',
                'payload' => [
                    'member_id' => $alert->member_id,
                    'alert_id' => $alert->id,
                    'active' => $alert->active,
                ],
            ]);
        });

        $causer = Filament::auth()->user() ?? auth()->user() ?? $alert->member;
        $alert->addComment(
            $alert->active
                ? __('models/job-alert.comments.toggled_active', ['name' => $causer->name ?? 'Miembro'])
                : __('models/job-alert.comments.toggled_inactive', ['name' => $causer->name ?? 'Miembro'])
        );

        return $alert->fresh();
    }
}
