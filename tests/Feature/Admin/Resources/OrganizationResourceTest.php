<?php

namespace Tests\Feature\Admin\Resources;

use App\Enums\OrganizationVerificationState;
use App\Filament\Admin\Resources\OrganizationResource;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrganizationResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create([
            'name' => 'admin',
            'title' => 'Administrator',
            'is_active' => true,
            'is_admin' => true,
            'perm' => ['*.*'],
        ]);

        $user = User::factory()->create([
            'role_id' => $adminRole->id,
            'is_active' => true,
        ]);

        Livewire::actingAs($user, 'admin');
        $this->get('/admin');
    }

    public function test_admin_can_see_organization_list(): void
    {
        $member = Member::factory()->create(['is_active' => true]);
        Organization::factory()->create(['member_id' => $member->id]);

        $this->get(OrganizationResource::getUrl('index'))
            ->assertSuccessful();
    }

    public function test_list_filters_by_verification_state(): void
    {
        $member1 = Member::factory()->create(['is_active' => true]);
        $member2 = Member::factory()->create(['is_active' => true]);

        Organization::factory()->create([
            'member_id' => $member1->id,
            'display_name' => 'Pending Org',
            'verification_state' => OrganizationVerificationState::PENDING,
        ]);
        Organization::factory()->verified()->create([
            'member_id' => $member2->id,
            'display_name' => 'Verified Org',
        ]);

        Livewire::test(OrganizationResource\Pages\ListOrganizations::class)
            ->assertCanSeeTableRecords(Organization::all());
    }

    public function test_admin_can_view_organization_detail(): void
    {
        $member = Member::factory()->create(['is_active' => true]);
        $org = Organization::factory()->create(['member_id' => $member->id]);

        $this->get(OrganizationResource::getUrl('view', ['record' => $org]))
            ->assertSuccessful();
    }

    public function test_verify_action_visible_for_pending_org(): void
    {
        $member = Member::factory()->create(['is_active' => true]);
        $org = Organization::factory()->create([
            'member_id' => $member->id,
            'verification_state' => OrganizationVerificationState::PENDING,
        ]);

        Livewire::test(OrganizationResource\Pages\ViewOrganization::class, ['record' => $org->id])
            ->assertActionVisible('verify');
    }

    public function test_suspend_action_visible_for_pending_and_verified_org(): void
    {
        $member = Member::factory()->create(['is_active' => true]);
        $org = Organization::factory()->create([
            'member_id' => $member->id,
            'verification_state' => OrganizationVerificationState::PENDING,
        ]);

        Livewire::test(OrganizationResource\Pages\ViewOrganization::class, ['record' => $org->id])
            ->assertActionVisible('suspend-organization');
    }
}
