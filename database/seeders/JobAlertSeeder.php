<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\JobAlertFrequency;
use App\Models\Category;
use App\Models\JobAlert;
use App\Models\Member;
use Illuminate\Database\Seeder;

class JobAlertSeeder extends Seeder
{
    public function run(): void
    {
        $member = Member::query()->orderBy('id')->first();
        if (! $member) {
            return;
        }

        if (JobAlert::query()->where('member_id', $member->id)->exists()) {
            return;
        }

        $category = Category::query()->where('scope', 'JobListing')->orderBy('id')->first();

        foreach ([JobAlertFrequency::Daily, JobAlertFrequency::Weekly, JobAlertFrequency::Instant] as $frequency) {
            JobAlert::factory()->create([
                'member_id' => $member->id,
                'category_id' => $category?->id,
                'city' => 'Lima',
                'frequency' => $frequency->value,
                'active' => true,
            ]);
        }
    }
}
