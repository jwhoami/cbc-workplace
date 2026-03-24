<?php

namespace Tests\Feature\Member\Resources;

use App\Enums\ContractType;
use App\Enums\OrganizationVerificationState;
use App\Enums\WorkModality;
use App\Filament\Member\Resources\JobListingResource;
use App\Models\Category;
use App\Models\JobListing;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class JobListingResourceTest extends TestCase
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

    public function test_can_render_list_page(): void
    {
        Livewire::test(JobListingResource\Pages\ListJobListings::class)
            ->assertSuccessful();
    }

    public function test_can_render_create_page(): void
    {
        Livewire::test(JobListingResource\Pages\CreateJobListing::class)
            ->assertSuccessful();
    }

    public function test_can_create_listing_with_required_fields(): void
    {
        $category = Category::create([
            'name' => 'Tecnología',
            'slug' => 'tecnologia',
            'scope' => 'JobListing',
            'order' => 1,
        ]);

        Livewire::test(JobListingResource\Pages\CreateJobListing::class)
            ->fillForm([
                'title' => 'Desarrollador PHP Senior',
                'description' => 'Buscamos un desarrollador PHP con experiencia en Laravel.',
                'requirements' => 'Mínimo 3 años de experiencia.',
                'contract_type' => ContractType::FULL_TIME->value,
                'work_modality' => WorkModality::HYBRID->value,
                'city' => 'Ciudad de Panamá',
                'province' => 'Panamá',
                'application_deadline' => now()->addMonth()->format('Y-m-d'),
                'category_id' => [$category->id],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('job_listings', [
            'organization_id' => $this->organization->id,
            'member_id' => $this->member->id,
            'title' => 'Desarrollador PHP Senior',
        ]);
    }

    public function test_can_edit_draft_listing(): void
    {
        $listing = JobListing::factory()->forOrganization($this->organization)->draft()->create();

        Livewire::test(JobListingResource\Pages\EditJobListing::class, ['record' => $listing->getRouteKey()])
            ->assertSuccessful();
    }

    public function test_member_cannot_see_other_organizations_listings(): void
    {
        $otherMember = Member::factory()->create([
            'is_active' => true,
            'is_blocked' => false,
            'role_id' => $this->member->role_id,
        ]);
        $otherOrg = Organization::factory()->create([
            'member_id' => $otherMember->id,
            'verification_state' => OrganizationVerificationState::VERIFIED,
            'verified_at' => now(),
        ]);

        $ownListing = JobListing::factory()->forOrganization($this->organization)->draft()->create();
        $otherListing = JobListing::factory()->forOrganization($otherOrg)->draft()->create();

        Livewire::test(JobListingResource\Pages\ListJobListings::class)
            ->assertCanSeeTableRecords([$ownListing])
            ->assertCanNotSeeTableRecords([$otherListing]);
    }

    public function test_can_edit_rejected_listing(): void
    {
        $listing = JobListing::factory()->forOrganization($this->organization)->rejected()->create();

        Livewire::test(JobListingResource\Pages\EditJobListing::class, ['record' => $listing->getRouteKey()])
            ->assertSuccessful();
    }

    public function test_can_view_rejected_listing_with_reason(): void
    {
        $listing = JobListing::factory()->forOrganization($this->organization)->rejected()->create([
            'approval_reason' => 'La descripción necesita más detalles.',
        ]);

        Livewire::test(JobListingResource\Pages\ViewJobListing::class, ['record' => $listing->getRouteKey()])
            ->assertSuccessful();
    }
}
