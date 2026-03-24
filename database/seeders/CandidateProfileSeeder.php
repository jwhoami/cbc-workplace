<?php

namespace Database\Seeders;

use App\Models\CandidateProfile;
use App\Models\Education;
use App\Models\Member;
use App\Models\WorkExperience;
use Illuminate\Database\Seeder;

class CandidateProfileSeeder extends Seeder
{
  public function run(): void
  {
    $member = Member::first();

    if (!$member) {
      return;
    }

    $profile = CandidateProfile::factory()->create([
      'member_id' => $member->id,
    ]);

    WorkExperience::factory()->count(2)->create([
      'candidate_profile_id' => $profile->id,
    ]);

    WorkExperience::factory()->current()->create([
      'candidate_profile_id' => $profile->id,
    ]);

    Education::factory()->count(2)->create([
      'candidate_profile_id' => $profile->id,
    ]);

    Education::factory()->inProgress()->create([
      'candidate_profile_id' => $profile->id,
    ]);
  }
}
