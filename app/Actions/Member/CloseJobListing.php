<?php

namespace App\Actions\Member;

use App\Enums\JobListingState;
use App\Helpers\Util;
use App\Models\JobListing;
use Lorisleiva\Actions\Concerns\AsAction;

class CloseJobListing
{
    use AsAction;

    public function handle(JobListing $jobListing): void
    {
        if ($jobListing->state !== JobListingState::ACTIVE) {
            throw new \Exception('Solo se pueden cerrar ofertas activas.');
        }

        $jobListing->state = JobListingState::CLOSED;
        $jobListing->closed_at = now();
        $jobListing->save();

        $jobListing->addComment('Oferta de empleo cerrada por el miembro');

        Util::getActivityLog('job-listing.close')
            ->performedOn($jobListing)
            ->log('Oferta de empleo cerrada');
    }
}
