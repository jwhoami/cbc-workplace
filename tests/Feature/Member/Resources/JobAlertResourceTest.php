<?php

declare(strict_types=1);

namespace Tests\Feature\Member\Resources;

use App\Enums\JobAlertFrequency;
use App\Filament\Member\Resources\JobAlertResource;
use App\Models\Category;
use App\Models\JobAlert;
use App\Models\Member;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class JobAlertResourceTest extends TestCase
{
    use RefreshDatabase;

    protected Member $member;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::create([
            'name' => 'member', 'title' => 'Member', 'is_active' => true,
            'is_admin' => false, 'perm' => [],
        ]);

        $this->member = Member::factory()->create([
            'is_active' => true, 'is_blocked' => false, 'role_id' => $role->id,
        ]);

        $this->category = Category::create([
            'name' => 'Diseño', 'scope' => 'JobListing', 'slug' => 'diseno', 'order' => 1,
        ]);

        Livewire::actingAs($this->member, 'member');
        $this->get('/member');
    }

    public function test_list_page_renders_for_authenticated_member(): void
    {
        Livewire::test(JobAlertResource\Pages\ListJobAlerts::class)
            ->assertSuccessful();
    }

    public function test_second_member_cannot_see_anothers_alerts(): void
    {
        $other = Member::factory()->create([
            'is_active' => true, 'is_blocked' => false,
            'role_id' => $this->member->role_id,
        ]);

        JobAlert::factory()->create([
            'member_id' => $other->id,
            'category_id' => $this->category->id,
            'frequency' => JobAlertFrequency::Daily->value,
        ]);

        Livewire::test(JobAlertResource\Pages\ListJobAlerts::class)
            ->assertCanNotSeeTableRecords(JobAlert::all());
    }

    public function test_create_page_creates_alert_via_action(): void
    {
        Livewire::test(JobAlertResource\Pages\CreateJobAlert::class)
            ->fillForm([
                'category_id' => $this->category->id,
                'city' => 'Lima',
                'frequency' => JobAlertFrequency::Daily->value,
                'active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('job_alerts', [
            'member_id' => $this->member->id,
            'category_id' => $this->category->id,
            'city' => 'Lima',
            'frequency' => JobAlertFrequency::Daily->value,
        ]);
    }

    public function test_toggle_action_flips_active_state(): void
    {
        $alert = JobAlert::factory()->create([
            'member_id' => $this->member->id,
            'category_id' => $this->category->id,
            'frequency' => JobAlertFrequency::Daily->value,
            'active' => true,
        ]);

        Livewire::test(JobAlertResource\Pages\ListJobAlerts::class)
            ->callTableAction('toggle-active', $alert);

        $this->assertFalse($alert->fresh()->active);
    }

    public function test_delete_action_removes_alert(): void
    {
        $alert = JobAlert::factory()->create([
            'member_id' => $this->member->id,
            'category_id' => $this->category->id,
            'frequency' => JobAlertFrequency::Daily->value,
        ]);

        Livewire::test(JobAlertResource\Pages\ListJobAlerts::class)
            ->callTableAction('delete', $alert);

        $this->assertDatabaseMissing('job_alerts', ['id' => $alert->id]);
    }

    public function test_quota_exception_surfaces_as_filament_notification(): void
    {
        for ($i = 0; $i < 10; $i++) {
            JobAlert::factory()->create([
                'member_id' => $this->member->id,
                'category_id' => null,
                'city' => "City{$i}",
                'frequency' => JobAlertFrequency::Daily->value,
            ]);
        }

        Livewire::test(JobAlertResource\Pages\CreateJobAlert::class)
            ->fillForm([
                'category_id' => $this->category->id,
                'city' => 'Lima',
                'frequency' => JobAlertFrequency::Daily->value,
                'active' => true,
            ])
            ->call('create')
            ->assertNotified();

        $this->assertSame(10, JobAlert::query()->where('member_id', $this->member->id)->count());
    }
}
