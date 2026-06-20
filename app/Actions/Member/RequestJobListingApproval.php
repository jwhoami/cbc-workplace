<?php

namespace App\Actions\Member;

use App\Enums\JobListingState;
use App\Enums\OrganizationVerificationState;
use App\Helpers\AppUtil;
use App\Helpers\Util;
use App\Mail\Admin\JobListingSubmitted;
use App\Models\JobListing;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;

class RequestJobListingApproval
{
    use AsAction;

    public function handle(JobListing $jobListing): void
    {
        if ($jobListing->organization->verification_state !== OrganizationVerificationState::VERIFIED) {
            throw new \Exception(__('models/job-listing.notifications.org_not_verified'));
        }

        if (! $jobListing->canSubmit()) {
            throw new \Exception(__('models/job-listing.notifications.cannot_edit'));
        }

        if ($jobListing->application_deadline->isPast()) {
            throw new \Exception(__('models/job-listing.notifications.deadline_past'));
        }

        $jobListing->state = JobListingState::PENDING;
        $jobListing->save();

        $jobListing->addComment('Solicitud de aprobación de oferta de empleo');

        Util::getActivityLog('job-listing.submit')
            ->performedOn($jobListing)
            ->log('Oferta de empleo enviada a aprobación');

        $approvers = AppUtil::getJobListingApprovers();

        foreach ($approvers as $user) {
            Mail::to($user)->send(new JobListingSubmitted($jobListing));
        }
    }
}
