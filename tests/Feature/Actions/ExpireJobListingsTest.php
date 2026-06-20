<?php

namespace Tests\Feature\Actions;

use App\Actions\ExpireJobListings;
use App\Enums\JobListingState;
use App\Enums\OrganizationVerificationState;
use App\Models\JobListing;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpireJobListingsTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();

        $memberRole = Role::create([
            'name' => 'member',
            'title' => 'Member',
            'is_active' => true,
            'is_admin' => false,
            'perm' => [],
        ]);

        $member = Member::factory()->create([
            'is_active' => true,
            'is_blocked' => false,
            'role_id' => $memberRole->id,
        ]);

        $this->organization = Organization::factory()->create([
            'member_id' => $member->id,
            'verification_state' => OrganizationVerificationState::VERIFIED,
            'verified_at' => now(),
        ]);
    }

    public function test_expires_active_listings_past_deadline(): void
    {
        $listing = JobListing::factory()->forOrganization($this->organization)->active()->create([
            'application_deadline' => now()->subDay(),
        ]);

        $count = ExpireJobListings::run();

        $listing->refresh();
        $this->assertEquals(JobListingState::EXPIRED, $listing->state);
        $this->assertEquals(1, $count);
    }

    public function test_skips_non_active_listings(): void
    {
        JobListing::factory()->forOrganization($this->organization)->draft()->create([
            'application_deadline' => now()->subDay(),
        ]);

        $count = ExpireJobListings::run();

        $this->assertEquals(0, $count);
    }

    public function test_skips_future_deadline_listings(): void
    {
        $listing = JobListing::factory()->forOrganization($this->organization)->active()->create([
            'application_deadline' => now()->addWeek(),
        ]);

        $count = ExpireJobListings::run();

        $listing->refresh();
        $this->assertEquals(JobListingState::ACTIVE, $listing->state);
        $this->assertEquals(0, $count);
    }

    public function test_batch_expires_multiple_listings(): void
    {
        JobListing::factory()->forOrganization($this->organization)->active()->count(3)->create([
            'application_deadline' => now()->subDay(),
        ]);

        $count = ExpireJobListings::run();

        $this->assertEquals(3, $count);
        $this->assertEquals(3, JobListing::where('state', JobListingState::EXPIRED)->count());
    }
}
