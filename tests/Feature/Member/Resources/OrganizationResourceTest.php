<?php

namespace Tests\Feature\Member\Resources;

use App\Enums\OrganizationVerificationState;
use App\Filament\Member\Resources\OrganizationResource;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrganizationResourceTest extends TestCase
{
  use RefreshDatabase;

  protected Member $member;

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

    Livewire::actingAs($this->member, 'member');
    $this->get('/member');
  }

  public function test_member_can_create_organization(): void
  {
    Livewire::test(OrganizationResource\Pages\CreateOrganization::class)
      ->fillForm([
        'legal_name' => 'Iglesia Test Legal',
        'display_name' => 'Iglesia Test',
        'type' => \App\Enums\OrganizationType::CHURCH->value,
        'description' => 'Una iglesia de prueba',
        'email_contact' => 'test@iglesia.com',
        'city' => 'Ciudad de Panamá',
        'province' => 'Panamá',
        'country' => 'Panama',
      ])
      ->call('create')
      ->assertHasNoFormErrors();

    $this->assertDatabaseHas('organizations', [
      'member_id' => $this->member->id,
      'legal_name' => 'Iglesia Test Legal',
      'display_name' => 'Iglesia Test',
    ]);
  }

  public function test_member_can_edit_own_organization(): void
  {
    $org = Organization::factory()->create([
      'member_id' => $this->member->id,
    ]);

    Livewire::test(OrganizationResource\Pages\EditOrganization::class, ['record' => $org->id])
      ->fillForm([
        'display_name' => 'Updated Name',
      ])
      ->call('save')
      ->assertHasNoFormErrors();

    $this->assertDatabaseHas('organizations', [
      'id' => $org->id,
      'display_name' => 'Updated Name',
    ]);
  }

  public function test_member_cannot_create_second_organization(): void
  {
    Organization::factory()->create([
      'member_id' => $this->member->id,
    ]);

    // CreateOrganization redirects to edit if org exists
    Livewire::test(OrganizationResource\Pages\CreateOrganization::class)
      ->assertRedirect();
  }

  public function test_organization_starts_with_pending_state(): void
  {
    Livewire::test(OrganizationResource\Pages\CreateOrganization::class)
      ->fillForm([
        'legal_name' => 'New Org Legal',
        'display_name' => 'New Org',
        'type' => \App\Enums\OrganizationType::NONPROFIT->value,
        'description' => 'Una organización nueva',
        'email_contact' => 'new@org.com',
        'city' => 'Colón',
        'province' => 'Colón',
        'country' => 'Panama',
      ])
      ->call('create')
      ->assertHasNoFormErrors();

    $org = Organization::where('member_id', $this->member->id)->first();
    $this->assertNotNull($org);
    $this->assertEquals(OrganizationVerificationState::PENDING, $org->verification_state);
  }

  public function test_request_verification_action_visible_for_pending_org(): void
  {
    $org = Organization::factory()->create([
      'member_id' => $this->member->id,
      'verification_state' => OrganizationVerificationState::PENDING,
    ]);

    Livewire::test(OrganizationResource\Pages\EditOrganization::class, ['record' => $org->id])
      ->assertActionVisible('request-verification');
  }
}
