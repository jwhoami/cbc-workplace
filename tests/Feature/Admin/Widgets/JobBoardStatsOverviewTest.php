<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Widgets;

use App\Enums\OrganizationVerificationState;
use App\Filament\Admin\Widgets\JobBoardStatsOverview;
use App\Models\Application;
use App\Models\CandidateProfile;
use App\Models\JobListing;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class JobBoardStatsOverviewTest extends TestCase
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
            'perm' => ['Admin.Organization.viewAny'],
        ]);

        $this->admin = User::factory()->create(['role_id' => $adminRole->id, 'is_active' => true]);
        $this->nonAdmin = User::factory()->create(['role_id' => $nonAdminRole->id, 'is_active' => true]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_widget_renders_zero_values_on_empty_database(): void
    {
        Livewire::actingAs($this->admin, 'admin');
        $stats = $this->extractStats();

        $this->assertCount(4, $stats);
        $this->assertSame('0', $stats[0]->getValue());
        $this->assertSame('0 (0)', $stats[1]->getValue());
        $this->assertSame('0', $stats[2]->getValue());
        $this->assertSame('0', $stats[3]->getValue());
    }

    public function test_widget_reports_expected_counts_from_seed(): void
    {
        $this->seedUniverse();

        Livewire::actingAs($this->admin, 'admin');
        $stats = $this->extractStats();

        $this->assertSame('5', $stats[0]->getValue());
        $this->assertSame('12 (3)', $stats[1]->getValue());
        $this->assertSame('25', $stats[2]->getValue());
        $this->assertSame('7', $stats[3]->getValue());
    }

    public function test_non_admin_user_cannot_view_widget(): void
    {
        Livewire::actingAs($this->nonAdmin, 'admin');

        $this->assertFalse(JobBoardStatsOverview::canView());
    }

    public function test_admin_can_view_widget(): void
    {
        Livewire::actingAs($this->admin, 'admin');

        $this->assertTrue(JobBoardStatsOverview::canView());
    }

    private function extractStats(): array
    {
        $widget = new JobBoardStatsOverview;

        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('getStats');
        $method->setAccessible(true);

        return $method->invoke($widget);
    }

    private function seedUniverse(): void
    {
        $verifiedMembers = Member::factory()->count(3)->create(['is_active' => true]);
        $pendingMembers = Member::factory()->count(9)->create(['is_active' => true]);

        foreach ($verifiedMembers as $m) {
            Organization::factory()->verified()->create(['member_id' => $m->id]);
        }
        foreach ($pendingMembers as $m) {
            Organization::factory()->create([
                'member_id' => $m->id,
                'verification_state' => OrganizationVerificationState::PENDING,
            ]);
        }

        $candidateMembers = Member::factory()->count(5)->create(['is_active' => true]);
        foreach ($candidateMembers as $m) {
            CandidateProfile::factory()->create(['member_id' => $m->id]);
        }

        $firstVerified = Organization::query()->where('verification_state', OrganizationVerificationState::VERIFIED)->first();
        JobListing::factory()->count(25)->active()->create([
            'organization_id' => $firstVerified->id,
            'member_id' => $firstVerified->member_id,
        ]);

        $offer = JobListing::query()->first();
        Member::factory()->count(7)->create(['is_active' => true])->each(function (Member $candidate) use ($offer) {
            Application::factory()->create([
                'job_listing_id' => $offer->id,
                'member_id' => $candidate->id,
                'submitted_at' => now()->subHours(rand(1, 23)),
            ]);
        });

        // Older applications outside the 24h window.
        Member::factory()->count(11)->create(['is_active' => true])->each(function (Member $candidate) use ($offer) {
            Application::factory()->create([
                'job_listing_id' => $offer->id,
                'member_id' => $candidate->id,
                'submitted_at' => now()->subDays(rand(2, 30)),
            ]);
        });
    }
}
