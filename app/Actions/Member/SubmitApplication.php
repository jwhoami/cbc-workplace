<?php

namespace App\Actions\Member;

use App\Enums\ApplicationStatus;
use App\Enums\JobListingState;
use App\Helpers\Util;
use App\Mail\Member\ApplicationSubmitted;
use App\Mail\Organization\ApplicationReceived;
use App\Models\Application;
use App\Models\JobListing;
use App\Models\Member;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\Concerns\AsAction;

class SubmitApplication
{
    use AsAction;

    public function handle(Member $member, JobListing $listing, array $data = []): Application
    {
        $profile = $member->candidateProfile;
        if (! $profile) {
            throw new \Exception(__('models/application.notifications.no_profile'));
        }

        if ($listing->state !== JobListingState::ACTIVE
            || ($listing->application_deadline && $listing->application_deadline->isPast())
        ) {
            throw new \Exception(__('models/application.notifications.listing_inactive'));
        }

        $alreadyApplied = Application::query()
            ->where('job_listing_id', $listing->id)
            ->where('member_id', $member->id)
            ->exists();

        if ($alreadyApplied) {
            throw new \Exception(__('models/application.notifications.duplicate'));
        }

        $this->validateScreeningAnswers($listing, $data['screening_answers'] ?? []);

        try {
            $application = DB::transaction(function () use ($member, $listing, $profile, $data) {
                $application = (new Application)->forceFill([
                    'job_listing_id' => $listing->id,
                    'member_id' => $member->id,
                    'candidate_profile_id' => $profile->id,
                    'cover_letter' => $data['cover_letter'] ?? null,
                    'screening_answers' => $data['screening_answers'] ?? null,
                    'cv_snapshot_path' => null,
                    'cv_snapshot_filename' => null,
                    'candidate_name_snapshot' => $member->name,
                    'candidate_email_snapshot' => $member->email,
                    'status' => ApplicationStatus::RECEIVED,
                    'submitted_at' => now(),
                ]);
                $application->save();

                $this->copyCvSnapshot($application, $profile);

                return $application;
            });
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                throw new \Exception(__('models/application.notifications.duplicate'));
            }
            throw $e;
        }

        $application->refresh();
        $application->addComment(__('models/application.comments.received'));

        Util::getActivityLog('application.create')
            ->performedOn($application)
            ->log('Postulación enviada');

        Mail::to($member)->send(new ApplicationSubmitted($application));
        Mail::to($listing->member)->send(new ApplicationReceived($application));

        return $application;
    }

    protected function validateScreeningAnswers(JobListing $listing, array $answers): void
    {
        $questions = collect($listing->screening_questions ?? [])
            ->map(fn ($q) => is_array($q) ? ($q['question'] ?? null) : $q)
            ->filter()
            ->values()
            ->all();

        if (empty($questions)) {
            return;
        }

        $answered = collect($answers)
            ->filter(fn ($a) => filled($a['answer'] ?? null))
            ->pluck('question')
            ->all();

        $missing = array_diff($questions, $answered);

        if (! empty($missing)) {
            throw new \Exception(__('models/application.validation.answer_required'));
        }
    }

    protected function copyCvSnapshot(Application $application, $profile): void
    {
        if (! $profile->cv_path) {
            return;
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($profile->cv_path)) {
            return;
        }

        $extension = pathinfo($profile->cv_path, PATHINFO_EXTENSION) ?: 'pdf';
        $destination = "applications/{$application->id}/cv.{$extension}";

        $disk->copy($profile->cv_path, $destination);

        $application->forceFill([
            'cv_snapshot_path' => $destination,
            'cv_snapshot_filename' => basename($profile->cv_path),
        ])->save();
    }
}
