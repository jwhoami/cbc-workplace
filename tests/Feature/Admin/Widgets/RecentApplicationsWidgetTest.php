<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Widgets;

use App\Filament\Admin\Widgets\RecentApplicationsWidget;
use App\Models\Application;
use App\Models\JobListing;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RecentApplicationsWidgetTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $nonAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create([
            'name' => 'admin', 'title' => 'Administrator',
            'is_active' => true, 'is_admin' => true, 'perm' => ['*.*'],
        ]);
        $nonAdminRole = Role::create([
            'name' => 'deacono', 'title' => 'Deacono',
            'is_active' => true, 'is_admin' => false, 'perm' => [],
        ]);

        $this->admin = User::factory()->create(['role_id' => $adminRole->id, 'is_active' => true]);
        $this->nonAdmin = User::factory()->create(['role_id' => $nonAdminRole->id, 'is_active' => true]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_query_returns_latest_ten_applications_ordered_desc(): void
    {
        $orgOwner = Member::factory()->create();
        $org = Organization::factory()->verified()->create(['member_id' => $orgOwner->id]);
        $listing = JobListing::factory()->active()->create([
            'organization_id' => $org->id,
            'member_id' => $orgOwner->id,
        ]);

        $candidates = Member::factory()->count(15)->create();
        foreach ($candidates as $i => $candidate) {
            Application::factory()->create([
                'job_listing_id' => $listing->id,
                'member_id' => $candidate->id,
                'submitted_at' => now()->subHours($i),
            ]);
        }

        Livewire::actingAs($this->admin, 'admin');
        $widget = new RecentApplicationsWidget;
        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('getTableQuery');
        $method->setAccessible(true);
        $rows = $method->invoke($widget)->get();

        $this->assertCount(10, $rows);

        for ($i = 0; $i < count($rows) - 1; $i++) {
            $this->assertTrue(
                $rows[$i]->submitted_at->greaterThanOrEqualTo($rows[$i + 1]->submitted_at),
                'Applications must be ordered by submitted_at DESC'
            );
        }
    }

    public function test_widget_returns_empty_when_no_applications(): void
    {
        Livewire::actingAs($this->admin, 'admin');
        $widget = new RecentApplicationsWidget;
        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('getTableQuery');
        $method->setAccessible(true);

        $this->assertCount(0, $method->invoke($widget)->get());
    }

    public function test_non_admin_cannot_view(): void
    {
        Livewire::actingAs($this->nonAdmin, 'admin');
        $this->assertFalse(RecentApplicationsWidget::canView());
    }

    public function test_admin_can_view(): void
    {
        Livewire::actingAs($this->admin, 'admin');
        $this->assertTrue(RecentApplicationsWidget::canView());
    }
}
