<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Resources;

use App\Filament\Admin\Resources\OrganizationResource\Pages\ViewOrganization;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrganizationResourceSuspendTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Organization $verifiedOrg;

    private Organization $suspendedOrg;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create([
            'name' => 'admin', 'title' => 'Administrator',
            'is_active' => true, 'is_admin' => true, 'perm' => ['*.*'],
        ]);
        $this->admin = User::factory()->create(['role_id' => $adminRole->id, 'is_active' => true]);

        $this->verifiedOrg = Organization::factory()->verified()->create([
            'member_id' => Member::factory()->create()->id,
        ]);
        $this->suspendedOrg = Organization::factory()->verifiedSuspended()->create([
            'member_id' => Member::factory()->create()->id,
        ]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Livewire::actingAs($this->admin, 'admin');
    }

    public function test_view_page_shows_suspend_header_action_for_active_org(): void
    {
        Livewire::test(ViewOrganization::class, ['record' => $this->verifiedOrg->getRouteKey()])
            ->assertActionVisible('suspend-organization')
            ->assertActionHidden('reactivate-organization');
    }

    public function test_view_page_shows_reactivate_header_action_for_suspended_org(): void
    {
        Livewire::test(ViewOrganization::class, ['record' => $this->suspendedOrg->getRouteKey()])
            ->assertActionVisible('reactivate-organization')
            ->assertActionHidden('suspend-organization');
    }

    public function test_suspend_modal_includes_reason_textarea_with_max_length(): void
    {
        Livewire::test(ViewOrganization::class, ['record' => $this->verifiedOrg->getRouteKey()])
            ->mountAction('suspend-organization')
            ->assertActionMounted('suspend-organization')
            ->assertFormFieldExists('reason', 'mountedActionForm');
    }

    public function test_submitting_suspend_action_runs_suspend_organization(): void
    {
        Livewire::test(ViewOrganization::class, ['record' => $this->verifiedOrg->getRouteKey()])
            ->callAction('suspend-organization', data: ['reason' => 'Razón de prueba']);

        $this->verifiedOrg->refresh();
        $this->assertTrue($this->verifiedOrg->is_suspended());
        $this->assertSame('Razón de prueba', $this->verifiedOrg->suspension_reason);
    }

    public function test_submitting_reactivate_action_clears_suspension(): void
    {
        Livewire::test(ViewOrganization::class, ['record' => $this->suspendedOrg->getRouteKey()])
            ->callAction('reactivate-organization');

        $this->suspendedOrg->refresh();
        $this->assertFalse($this->suspendedOrg->is_suspended());
    }
}
