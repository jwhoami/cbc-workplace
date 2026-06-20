<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Widgets;

use App\Enums\JobListingState;
use App\Filament\Admin\Widgets\PendingJobListingApprovalsWidget;
use App\Models\JobListing;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PendingJobListingApprovalsWidgetTest extends TestCase
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

    public function test_query_returns_only_pending_offers_limited_to_ten(): void
    {
        $member = Member::factory()->create();
        $org = Organization::factory()->verified()->create(['member_id' => $member->id]);

        JobListing::factory()->count(15)->pending()->create([
            'organization_id' => $org->id,
            'member_id' => $member->id,
        ]);
        JobListing::factory()->count(3)->active()->create([
            'organization_id' => $org->id,
            'member_id' => $member->id,
        ]);

        Livewire::actingAs($this->admin, 'admin');
        $widget = new PendingJobListingApprovalsWidget;
        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('getTableQuery');
        $method->setAccessible(true);
        $rows = $method->invoke($widget)->get();

        $this->assertCount(10, $rows);
        foreach ($rows as $row) {
            $this->assertEquals(JobListingState::PENDING, $row->state);
            $this->assertNotNull($row->organization);
        }
    }

    public function test_non_admin_cannot_view(): void
    {
        Livewire::actingAs($this->nonAdmin, 'admin');

        $this->assertFalse(PendingJobListingApprovalsWidget::canView());
    }

    public function test_admin_can_view(): void
    {
        Livewire::actingAs($this->admin, 'admin');

        $this->assertTrue(PendingJobListingApprovalsWidget::canView());
    }
}
