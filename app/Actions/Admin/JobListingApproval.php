<?php

namespace App\Actions\Admin;

use App\Enums\JobListingState;
use App\Helpers\Util;
use App\Mail\Member\JobListingApproved;
use App\Mail\Member\JobListingRejected;
use App\Models\JobListing;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;

class JobListingApproval
{
    use AsAction;

    public function handle(JobListing $jobListing, array $data): void
    {
        $decision = JobListingState::from($data['decision']);

        if ($jobListing->state !== JobListingState::PENDING) {
            throw new \Exception('Solo se pueden aprobar o rechazar ofertas en estado pendiente.');
        }

        if (! in_array($decision, [JobListingState::ACTIVE, JobListingState::REJECTED])) {
            throw new \Exception('Decisión inválida.');
        }

        $decision === JobListingState::ACTIVE
          ? $this->approve($jobListing, $data['approval_reason'] ?? null)
          : $this->reject($jobListing, $data['approval_reason']);
    }

    private function approve(JobListing $jobListing, ?string $reason): void
    {
        $jobListing->state = JobListingState::ACTIVE;
        $jobListing->published_at = now();
        $jobListing->approval_by = auth()->user()->name;
        $jobListing->approval_at = now();
        $jobListing->approval_reason = $reason;
        $jobListing->save();

        $jobListing->addComment('Decisión de aprobación: APROBADO'.($reason ? ", Memo: {$reason}" : ''));

        Util::getActivityLog('job-listing.approve')
            ->performedOn($jobListing)
            ->log('Oferta de empleo aprobada');

        Mail::to($jobListing->member)->send(new JobListingApproved($jobListing));

        // Spec 008 — fan out to instant-alert listeners after all approval-side
        // work has committed/queued. Must be the final statement in approve().
        \App\Events\JobListingApproved::dispatch($jobListing);
    }

    private function reject(JobListing $jobListing, string $reason): void
    {
        $jobListing->state = JobListingState::REJECTED;
        $jobListing->approval_by = auth()->user()->name;
        $jobListing->approval_at = now();
        $jobListing->approval_reason = $reason;
        $jobListing->save();

        $jobListing->addComment("Decisión de aprobación: RECHAZADO, Memo: {$reason}");

        Util::getActivityLog('job-listing.reject')
            ->performedOn($jobListing)
            ->log('Oferta de empleo rechazada');

        Mail::to($jobListing->member)->send(new JobListingRejected($jobListing));
    }
}
