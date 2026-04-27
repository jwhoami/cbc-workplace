<?php

namespace Database\Seeders;

use App\Enums\ApplicationStatus;
use App\Enums\JobListingState;
use App\Models\Application;
use App\Models\CandidateProfile;
use App\Models\JobListing;
use App\Models\Member;
use Illuminate\Database\Seeder;

class ApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $listings = JobListing::query()
            ->where('state', JobListingState::ACTIVE)
            ->get();

        if ($listings->isEmpty()) {
            $this->command?->warn('ApplicationSeeder: no active job listings found. Skipping.');

            return;
        }

        $candidates = Member::query()
            ->whereHas('candidateProfile')
            ->limit(8)
            ->get();

        if ($candidates->count() === 0) {
            $candidates = collect();
            for ($i = 0; $i < 5; $i++) {
                $member = Member::factory()->create([
                    'is_active' => true,
                    'is_blocked' => false,
                    'name' => fake()->name(),
                    'email' => 'candidate'.$i.'@seed.local',
                ]);
                CandidateProfile::factory()->create(['member_id' => $member->id]);
                $candidates->push($member);
            }
        }

        $statuses = [
            ApplicationStatus::RECEIVED,
            ApplicationStatus::IN_REVIEW,
            ApplicationStatus::INTERVIEW,
            ApplicationStatus::REJECTED,
            ApplicationStatus::ACCEPTED,
        ];

        $created = 0;
        foreach ($listings->take(3) as $listing) {
            foreach ($candidates->take(5) as $idx => $candidate) {
                if ($listing->member_id === $candidate->id) {
                    continue;
                }
                if (Application::where('job_listing_id', $listing->id)->where('member_id', $candidate->id)->exists()) {
                    continue;
                }

                $status = $statuses[$idx % count($statuses)];
                Application::factory()->create([
                    'job_listing_id' => $listing->id,
                    'member_id' => $candidate->id,
                    'candidate_profile_id' => $candidate->candidateProfile?->id,
                    'candidate_name_snapshot' => $candidate->name,
                    'candidate_email_snapshot' => $candidate->email,
                    'cover_letter' => fake()->paragraph(3),
                    'status' => $status,
                    'last_status_changed_at' => $status === ApplicationStatus::RECEIVED ? null : now()->subDays(rand(1, 14)),
                    'last_status_changed_by' => $status === ApplicationStatus::RECEIVED ? null : fake()->name(),
                ]);
                $created++;
            }
        }

        $this->command?->info("ApplicationSeeder: created {$created} application(s).");
    }
}
