<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Widgets;

use App\Enums\OrganizationVerificationState;
use App\Filament\Admin\Widgets\PendingOrganizationVerificationsWidget;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PendingOrganizationVerificationsWidgetTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $nonAdmin;

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

        $nonAdminRole = Role::create([
            'name' => 'deacono',
            'title' => 'Deacono',
            'is_active' => true,
            'is_admin' => false,
            'perm' => [],
        ]);

        $this->admin = User::factory()->create(['role_id' => $adminRole->id, 'is_active' => true]);
        $this->nonAdmin = User::factory()->create(['role_id' => $nonAdminRole->id, 'is_active' => true]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_query_returns_only_pending_orgs_without_suspension_flag(): void
    {
        $pendingMembers = Member::factory()->count(2)->create();
        foreach ($pendingMembers as $m) {
            Organization::factory()->create([
                'member_id' => $m->id,
                'verification_state' => OrganizationVerificationState::PENDING,
            ]);
        }

        $verifiedMember = Member::factory()->create();
        Organization::factory()->verified()->create(['member_id' => $verifiedMember->id]);

        $suspendedPendingMember = Member::factory()->create();
        Organization::factory()->pendingSuspended()->create(['member_id' => $suspendedPendingMember->id]);

        Livewire::actingAs($this->admin, 'admin');
        $widget = new PendingOrganizationVerificationsWidget;
        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('getTableQuery');
        $method->setAccessible(true);
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = $method->invoke($widget);

        $rows = $query->get();

        $this->assertCount(2, $rows);
        foreach ($rows as $row) {
            $this->assertEquals(OrganizationVerificationState::PENDING, $row->verification_state);
            $this->assertNull($row->suspended_at);
        }
    }

    public function test_query_is_limited_to_ten_rows(): void
    {
        $members = Member::factory()->count(15)->create();
        foreach ($members as $m) {
            Organization::factory()->create([
                'member_id' => $m->id,
                'verification_state' => OrganizationVerificationState::PENDING,
            ]);
        }

        Livewire::actingAs($this->admin, 'admin');
        $widget = new PendingOrganizationVerificationsWidget;
        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('getTableQuery');
        $method->setAccessible(true);
        $query = $method->invoke($widget);

        $this->assertCount(10, $query->get());
    }

    public function test_non_admin_cannot_view(): void
    {
        Livewire::actingAs($this->nonAdmin, 'admin');

        $this->assertFalse(PendingOrganizationVerificationsWidget::canView());
    }

    public function test_admin_can_view(): void
    {
        Livewire::actingAs($this->admin, 'admin');

        $this->assertTrue(PendingOrganizationVerificationsWidget::canView());
    }
}
