<?php

declare(strict_types=1);

namespace App\Actions\Member;

use App\Enums\PublicEventKind;
use App\Exceptions\DuplicateAlertException;
use App\Helpers\DiacriticFolder;
use App\Models\JobAlert;
use App\Models\PublicEvent;
use App\Rules\JobListingCategory;
use App\Rules\SingleLineCity;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateJobAlertAction
{
    use AsAction;

    public function handle(JobAlert $alert, array $attributes): JobAlert
    {
        $data = Validator::make($attributes, [
            'category_id' => ['nullable', 'integer', new JobListingCategory],
            'city' => ['nullable', new SingleLineCity],
            'frequency' => ['required', 'integer', 'in:1,2,3'],
            'active' => ['sometimes', 'boolean'],
        ])->validate();

        $cityFolded = ! empty($data['city']) ? DiacriticFolder::fold((string) $data['city']) : null;

        $duplicate = JobAlert::query()
            ->where('member_id', $alert->member_id)
            ->where('id', '!=', $alert->id)
            ->where('category_id', $data['category_id'] ?? null)
            ->where('city_folded', $cityFolded)
            ->where('frequency', (int) $data['frequency'])
            ->first();

        if ($duplicate) {
            throw new DuplicateAlertException($duplicate->id);
        }

        $changedFields = [];

        DB::transaction(function () use ($alert, $data, &$changedFields) {
            $alert->fill([
                'category_id' => $data['category_id'] ?? null,
                'city' => $data['city'] ?? null,
                'frequency' => (int) $data['frequency'],
            ]);

            if (array_key_exists('active', $data)) {
                $alert->active = (bool) $data['active'];
            }

            $changedFields = array_keys($alert->getDirty());
            $alert->save();

            PublicEvent::create([
                'kind' => PublicEventKind::AlertEdited,
                'correlation_id' => (string) Str::uuid(),
                'occurred_at' => now(),
                'path' => '/member/job-alerts',
                'visitor_variant' => 'member',
                'payload' => [
                    'member_id' => $alert->member_id,
                    'alert_id' => $alert->id,
                    'changed' => $changedFields,
                ],
            ]);
        });

        $causer = Filament::auth()->user() ?? auth()->user() ?? $alert->member;
        $alert->addComment(__('models/job-alert.comments.edited', ['name' => $causer->name ?? 'Miembro']));

        return $alert->fresh();
    }
}
