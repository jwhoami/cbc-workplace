<?php

declare(strict_types=1);

namespace App\Actions\Member;

use App\Enums\PublicEventKind;
use App\Exceptions\AlertQuotaExceededException;
use App\Exceptions\DuplicateAlertException;
use App\Helpers\DiacriticFolder;
use App\Models\JobAlert;
use App\Models\Member;
use App\Models\PublicEvent;
use App\Rules\JobListingCategory;
use App\Rules\SingleLineCity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateJobAlertAction
{
    use AsAction;

    public function handle(Member $member, array $attributes): JobAlert
    {
        $data = Validator::make($attributes, [
            'category_id' => ['nullable', 'integer', new JobListingCategory],
            'city' => ['nullable', new SingleLineCity],
            'frequency' => ['required', 'integer', 'in:1,2,3'],
            'active' => ['sometimes', 'boolean'],
        ])->validate();

        $max = (int) config('alerts.max_alerts_per_member', 10);
        $current = JobAlert::query()->where('member_id', $member->id)->count();
        if ($current >= $max) {
            throw new AlertQuotaExceededException($current, $max);
        }

        $cityFolded = ! empty($data['city']) ? DiacriticFolder::fold((string) $data['city']) : null;

        $duplicate = JobAlert::query()
            ->where('member_id', $member->id)
            ->where('category_id', $data['category_id'] ?? null)
            ->where('city_folded', $cityFolded)
            ->where('frequency', (int) $data['frequency'])
            ->first();

        if ($duplicate) {
            throw new DuplicateAlertException($duplicate->id);
        }

        $alert = DB::transaction(function () use ($member, $data) {
            $alert = JobAlert::create([
                'member_id' => $member->id,
                'category_id' => $data['category_id'] ?? null,
                'city' => $data['city'] ?? null,
                'frequency' => (int) $data['frequency'],
                'active' => $data['active'] ?? true,
            ]);

            PublicEvent::create([
                'kind' => PublicEventKind::AlertCreated,
                'correlation_id' => (string) Str::uuid(),
                'occurred_at' => now(),
                'path' => '/member/job-alerts',
                'visitor_variant' => 'member',
                'payload' => [
                    'member_id' => $member->id,
                    'alert_id' => $alert->id,
                    'category_id' => $alert->category_id,
                    'city' => $alert->city,
                    'frequency' => $alert->frequency->value,
                ],
            ]);

            return $alert;
        });

        $alert->addComment(__('models/job-alert.comments.created', ['name' => $member->name]));

        return $alert->fresh();
    }
}
