<?php

namespace App\Actions;

use App\Enums\JobListingState;
use App\Helpers\Util;
use App\Models\JobListing;
use Lorisleiva\Actions\Concerns\AsAction;

class ExpireJobListings
{
    use AsAction;

    public function handle(): int
    {
        $listings = JobListing::query()
            ->where('state', JobListingState::ACTIVE)
            ->where('application_deadline', '<', now())
            ->get();

        foreach ($listings as $listing) {
            $listing->state = JobListingState::EXPIRED;
            $listing->save();

            Util::getActivityLog('job-listing.expire')
                ->performedOn($listing)
                ->log('Oferta de empleo expirada automáticamente');
        }

        return $listings->count();
    }
}
