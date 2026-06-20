<?php

declare(strict_types=1);

namespace Tests\Feature\Alerts;

use App\Actions\Alerts\DispatchWeeklyDigestAction;
use App\Enums\JobAlertFrequency;
use App\Models\Category;
use App\Models\JobAlert;
use App\Models\JobListing;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class DispatchWeeklyDigestActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_weekly_window_covers_seven_days_and_emits_log(): void
    {
        Mail::fake();
        Carbon::setTestNow('2026-05-11 09:00:00');

        $role = Role::create([
            'name' => 'member', 'title' => 'Member', 'is_active' => true,
            'is_admin' => false, 'perm' => [],
        ]);
        $owner = Member::factory()->create(['is_active' => true, 'role_id' => $role->id]);
        $org = Organization::factory()->create(['member_id' => $owner->id, 'is_active' => true]);
        $category = Category::create(['name' => 'Diseño', 'scope' => 'JobListing', 'slug' => 'diseno', 'order' => 1]);

        $member = Member::factory()->create(['is_active' => true, 'role_id' => $role->id]);
        JobAlert::factory()->create([
            'member_id' => $member->id,
            'category_id' => $category->id,
            'city' => null,
            'frequency' => JobAlertFrequency::Weekly->value,
        ]);

        // Offer published 4 days ago (in weekly window, out of daily window).
        $offer = JobListing::factory()->forOrganization($org)->active()->create([
            'application_deadline' => now()->addMonth(),
            'published_at' => now()->subDays(4),
        ]);
        $offer->categories()->attach($category);

        $summary = DispatchWeeklyDigestAction::run();

        $this->assertSame(1, $summary['sent']);
        Mail::assertQueuedCount(1);
        $this->assertTrue(Activity::query()->where('event', 'job-alert.dispatch.weekly')->exists());

        Carbon::setTestNow();
    }

    public function test_does_not_iterate_alerts_for_inactive_members(): void
    {
        Mail::fake();
        Carbon::setTestNow('2026-05-11 09:00:00');

        $role = Role::create([
            'name' => 'member', 'title' => 'Member', 'is_active' => true,
            'is_admin' => false, 'perm' => [],
        ]);
        $owner = Member::factory()->create(['is_active' => true, 'role_id' => $role->id]);
        Organization::factory()->create(['member_id' => $owner->id, 'is_active' => true]);

        $active = Member::factory()->create(['is_active' => true, 'role_id' => $role->id]);
        JobAlert::factory()->create([
            'member_id' => $active->id,
            'category_id' => null,
            'city' => null,
            'frequency' => JobAlertFrequency::Weekly->value,
        ]);

        $inactive = Member::factory()->create(['is_active' => false, 'role_id' => $role->id]);
        JobAlert::factory()->create([
            'member_id' => $inactive->id,
            'category_id' => null,
            'city' => null,
            'frequency' => JobAlertFrequency::Weekly->value,
        ]);

        $summary = DispatchWeeklyDigestAction::run();
        $this->assertSame(1, $summary['processed']);

        Carbon::setTestNow();
    }
}
