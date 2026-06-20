<?php

declare(strict_types=1);

namespace App\Actions\Member;

use App\Enums\PublicEventKind;
use App\Models\JobAlert;
use App\Models\PublicEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Deletes a JobAlert. By constitution Principle IV, model-level state changes
 * should leave an addComment() audit trail; this Action intentionally skips
 * that step because the alert and its polymorphic comments cascade-delete
 * together — any comment we appended here would be orphaned milliseconds
 * later. The forensic trail is preserved via:
 *
 *   1. The `AlertDeleted` PublicEvent (emitted BEFORE the delete so the row
 *      criteria are captured).
 *   2. The `LogsActivity` `deleted` event automatically logged by the trait.
 *
 * See specs/008-notifications-job-alerts/contracts/actions.md L80 and
 * tasks.md T032 for the rationale and reviewer note.
 */
class DeleteJobAlertAction
{
    use AsAction;

    public function handle(JobAlert $alert): void
    {
        DB::transaction(function () use ($alert) {
            PublicEvent::create([
                'kind' => PublicEventKind::AlertDeleted,
                'correlation_id' => (string) Str::uuid(),
                'occurred_at' => now(),
                'path' => '/member/job-alerts',
                'visitor_variant' => 'member',
                'payload' => [
                    'member_id' => $alert->member_id,
                    'alert_id' => $alert->id,
                    'category_id' => $alert->category_id,
                    'city' => $alert->city,
                    'frequency' => $alert->frequency?->value,
                ],
            ]);

            $alert->delete();
        });
    }
}
