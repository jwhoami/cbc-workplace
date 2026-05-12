<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\JobListingState;
use App\Helpers\DiacriticFolder;
use App\Models\JobListing;
use Illuminate\Support\Facades\Cache;

/**
 * Keeps the `title_folded` and `description_folded` columns coherent with the
 * source columns (FR-009b) and busts the public city-filter cache (FR-010c)
 * whenever a JobListing's state or organization changes in a way that affects
 * its public visibility.
 */
class JobListingObserver
{
    public function saving(JobListing $listing): void
    {
        if ($listing->isDirty('title') || $listing->title_folded === null || $listing->title_folded === '') {
            $listing->title_folded = DiacriticFolder::fold((string) $listing->title);
        }

        if ($listing->isDirty('description') || $listing->description_folded === null) {
            $listing->description_folded = DiacriticFolder::fold((string) $listing->description);
        }

        if ($listing->isDirty('city') || ($listing->city !== null && $listing->city_folded === null)) {
            $listing->city_folded = $listing->city !== null && $listing->city !== ''
                ? DiacriticFolder::fold((string) $listing->city)
                : null;
        }
    }

    public function saved(JobListing $listing): void
    {
        $stateChanged = $listing->wasChanged('state');
        $orgChanged = $listing->wasChanged('organization_id');
        $deadlineChanged = $listing->wasChanged('application_deadline');
        $cityChanged = $listing->wasChanged('city');

        if (! ($stateChanged || $orgChanged || $deadlineChanged || $cityChanged)) {
            return;
        }

        $touchesActiveSet = in_array($listing->state, [
            JobListingState::ACTIVE,
            JobListingState::EXPIRED,
            JobListingState::CLOSED,
        ], true);

        if ($touchesActiveSet) {
            Cache::forget('public.cities');
        }
    }
}
