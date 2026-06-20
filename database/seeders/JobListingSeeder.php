<?php

namespace Database\Seeders;

use App\Models\JobListing;
use App\Models\Organization;
use Illuminate\Database\Seeder;

class JobListingSeeder extends Seeder
{
    public function run(): void
    {
        $organizations = Organization::whereNotNull('verified_at')->get();

        if ($organizations->isEmpty()) {
            $this->command->warn('No verified organizations found. Skipping JobListingSeeder.');

            return;
        }

        foreach ($organizations as $organization) {
            JobListing::factory()->count(2)->forOrganization($organization)->draft()->create();
            JobListing::factory()->count(1)->forOrganization($organization)->pending()->create();
            JobListing::factory()->count(3)->forOrganization($organization)->active()->create();
            JobListing::factory()->count(1)->forOrganization($organization)->rejected()->create();
            JobListing::factory()->count(1)->forOrganization($organization)->closed()->create();
            JobListing::factory()->count(1)->forOrganization($organization)->expired()->create();
        }
    }
}
