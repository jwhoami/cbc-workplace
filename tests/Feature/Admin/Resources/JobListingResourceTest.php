<?php

namespace Tests\Feature\Admin\Resources;

use App\Enums\OrganizationVerificationState;
use App\Filament\Admin\Resources\JobListingResource;
use App\Models\JobListing;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class JobListingResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create([
            'name' => 'admin',
            'title' => 'Admin',
            'is_active' => true,
            'is_admin' => true,
            'perm' => [],
        ]);

        $memberRole = Role::create([
            'name' => 'member',
            'title' => 'Member',
            'is_active' => true,
            'is_admin' => false,
            'perm' => [],
        ]);

        $this->admin = User::factory()->create([
            'is_active' => true,
            'is_blocked' => false,
            'role_id' => $adminRole->id,
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

        Livewire::actingAs($this->admin, 'admin');
        $this->get('/admin');
    }

    public function test_can_render_list_page(): void
    {
        Livewire::test(JobListingResource\Pages\ListJobListings::class)
            ->assertSuccessful();
    }

    public function test_can_render_view_page(): void
    {
        $listing = JobListing::factory()->forOrganization($this->organization)->pending()->create();

        Livewire::test(JobListingResource\Pages\ViewJobListing::class, ['record' => $listing->getRouteKey()])
            ->assertSuccessful();
    }

    public function test_can_see_all_listings(): void
    {
        $listings = JobListing::factory()->count(3)->forOrganization($this->organization)->active()->create();

        Livewire::test(JobListingResource\Pages\ListJobListings::class)
            ->assertCanSeeTableRecords($listings);
    }
}
