<?php

namespace Tests\Feature\Member;

use App\Enums\JobListingState;
use App\Enums\OrganizationVerificationState;
use App\Models\JobListing;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Role;
use App\Filament\Member\Pages\BrowseJobs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BrowseJobsTest extends TestCase
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
            'email_verified_at' => now(),
        ]);

        $this->organization = Organization::factory()->create([
            'member_id' => $this->member->id,
            'verification_state' => OrganizationVerificationState::VERIFIED,
            'verified_at' => now(),
        ]);

        Livewire::actingAs($this->member, 'member');
    }

    public function test_authenticated_member_can_render_browse_jobs_page(): void
    {
        $this->get('/member/browse-jobs')
            ->assertSuccessful();

        Livewire::test(BrowseJobs::class)
            ->assertSuccessful();
    }

    public function test_it_lists_only_active_jobs_from_active_organizations(): void
    {
        $activeJob = JobListing::factory()->forOrganization($this->organization)->active()->create([
            'title' => 'Active Job Position'
        ]);

        $draftJob = JobListing::factory()->forOrganization($this->organization)->draft()->create([
            'title' => 'Draft Job Position'
        ]);

        Livewire::test(BrowseJobs::class)
            ->assertCanSeeTableRecords([$activeJob])
            ->assertCanNotSeeTableRecords([$draftJob]);
    }
}
