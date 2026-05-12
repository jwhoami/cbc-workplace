<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Actions\Alerts\CoalesceInstantMatchAction;
use App\Enums\JobAlertFrequency;
use App\Enums\JobListingState;
use App\Events\JobListingApproved;
use App\Helpers\DiacriticFolder;
use App\Models\JobAlert;
use Illuminate\Contracts\Queue\ShouldQueue;

class EvaluateInstantJobAlerts implements ShouldQueue
{
    public string $queue = 'instant';

    public function handle(JobListingApproved $event): void
    {
        $listing = $event->jobListing;

        if ($listing->state !== JobListingState::ACTIVE) {
            return;
        }

        $cityFolded = $listing->city_folded
            ?? ($listing->city ? DiacriticFolder::fold((string) $listing->city) : null);

        $categoryIds = $listing->categories()->pluck('categories.id')->all();

        $query = JobAlert::query()
            ->active()
            ->withFrequency(JobAlertFrequency::Instant)
            ->ofActiveMember();

        $query->where(function ($q) use ($categoryIds) {
            $q->whereNull('category_id');
            if (! empty($categoryIds)) {
                $q->orWhereIn('category_id', $categoryIds);
            }
        });

        $query->where(function ($q) use ($cityFolded) {
            $q->whereNull('city_folded');
            if ($cityFolded !== null) {
                $q->orWhere('city_folded', $cityFolded);
            }
        });

        $query->cursor()->each(function (JobAlert $alert) use ($listing) {
            CoalesceInstantMatchAction::dispatch($alert, $listing)->onQueue('instant');
        });
    }
}
