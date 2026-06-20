<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Resources;

use App\Filament\Admin\Resources\ApplicationResource;
use App\Filament\Admin\Resources\CandidateProfileResource;
use App\Filament\Admin\Resources\JobCategoryResource;
use App\Filament\Admin\Resources\JobListingResource;
use App\Filament\Admin\Resources\OrganizationResource;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NavigationGroupTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create([
            'name' => 'admin', 'title' => 'Administrator',
            'is_active' => true, 'is_admin' => true, 'perm' => ['*.*'],
        ]);
        $this->admin = User::factory()->create(['role_id' => $adminRole->id, 'is_active' => true]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Livewire::actingAs($this->admin, 'admin');
    }

    public function test_admin_panel_registers_bolsa_de_trabajo_group_between_administracion_and_emprendimientos(): void
    {
        $groups = Filament::getPanel('admin')->getNavigationGroups();
        $labels = array_map(fn ($g) => $g->getLabel(), $groups);

        $this->assertContains('Bolsa de Trabajo', $labels);
        $this->assertContains('Sistema', $labels);
        $this->assertContains('Administración', $labels);
        $this->assertContains('Emprendimientos', $labels);

        $admIdx = array_search('Administración', $labels, true);
        $btIdx = array_search('Bolsa de Trabajo', $labels, true);
        $empIdx = array_search('Emprendimientos', $labels, true);

        $this->assertGreaterThan($admIdx, $btIdx);
        $this->assertLessThan($empIdx, $btIdx);
    }

    public function test_all_five_bolsa_de_trabajo_resources_report_same_navigation_group_label(): void
    {
        $expected = 'Bolsa de Trabajo';

        $this->assertSame($expected, OrganizationResource::getNavigationGroup());
        $this->assertSame($expected, JobListingResource::getNavigationGroup());
        $this->assertSame($expected, ApplicationResource::getNavigationGroup());
        $this->assertSame($expected, JobCategoryResource::getNavigationGroup());
        $this->assertSame($expected, CandidateProfileResource::getNavigationGroup());
    }
}
