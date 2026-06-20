<?php

namespace Tests\Feature\Member\Actions;

use App\Actions\Member\CloseJobListing;
use App\Enums\JobListingState;
use App\Enums\OrganizationVerificationState;
use App\Models\JobListing;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CloseJobListingTest extends TestCase
{
    use RefreshDatabase;

    protected Member $member;

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

        $this->member = Member::factory()->create([
            'is_active' => true,
            'is_blocked' => false,
            'role_id' => $memberRole->id,
        ]);

        $this->organization = Organization::factory()->create([
            'member_id' => $this->member->id,
            'verification_state' => OrganizationVerificationState::VERIFIED,
            'verified_at' => now(),
        ]);

        Livewire::actingAs($this->member, 'member');
        $this->get('/member');
    }

    public function test_can_close_active_listing(): void
    {
        $listing = JobListing::factory()->forOrganization($this->organization)->active()->create();

        CloseJobListing::run($listing);

        $listing->refresh();
        $this->assertEquals(JobListingState::CLOSED, $listing->state);
        $this->assertNotNull($listing->closed_at);
    }

    public function test_fails_on_non_active_listing(): void
    {
        $listing = JobListing::factory()->forOrganization($this->organization)->draft()->create();

        $this->expectException(\Exception::class);
        CloseJobListing::run($listing);
    }

    public function test_fails_on_pending_listing(): void
    {
        $listing = JobListing::factory()->forOrganization($this->organization)->pending()->create();

        $this->expectException(\Exception::class);
        CloseJobListing::run($listing);
    }
}
