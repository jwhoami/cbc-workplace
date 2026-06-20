<?php

namespace Database\Factories;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\JobListing;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Application>
 */
class ApplicationFactory extends Factory
{
    protected $model = Application::class;

    public function definition(): array
    {
        return [
            'job_listing_id' => JobListing::factory(),
            'member_id' => Member::factory(),
            'candidate_profile_id' => null,
            'cover_letter' => fake()->text(1500),
            'screening_answers' => [
                ['question' => fake()->sentence(), 'answer' => fake()->text(300)],
            ],
            'cv_snapshot_path' => null,
            'cv_snapshot_filename' => null,
            'candidate_name_snapshot' => fake()->name(),
            'candidate_email_snapshot' => fake()->safeEmail(),
            'status' => ApplicationStatus::RECEIVED,
            'submitted_at' => fake()->dateTimeThisYear(),
            'last_status_changed_at' => null,
            'last_status_changed_by' => null,
            'anonymized_at' => null,
        ];
    }

    public function received(): static
    {
        return $this->state(['status' => ApplicationStatus::RECEIVED]);
    }

    public function inReview(): static
    {
        return $this->state([
            'status' => ApplicationStatus::IN_REVIEW,
            'last_status_changed_at' => now(),
            'last_status_changed_by' => fake()->name(),
        ]);
    }

    public function interview(): static
    {
        return $this->state([
            'status' => ApplicationStatus::INTERVIEW,
            'last_status_changed_at' => now(),
            'last_status_changed_by' => fake()->name(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state([
            'status' => ApplicationStatus::REJECTED,
            'last_status_changed_at' => now(),
            'last_status_changed_by' => fake()->name(),
        ]);
    }

    public function accepted(): static
    {
        return $this->state([
            'status' => ApplicationStatus::ACCEPTED,
            'last_status_changed_at' => now(),
            'last_status_changed_by' => fake()->name(),
        ]);
    }

    public function anonymized(): static
    {
        return $this->state([
            'candidate_name_snapshot' => __('models/application.snapshot.anonymized_name'),
            'candidate_email_snapshot' => null,
            'cv_snapshot_path' => null,
            'cv_snapshot_filename' => null,
            'anonymized_at' => now(),
        ]);
    }
}
