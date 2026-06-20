<?php

declare(strict_types=1);

namespace Tests\Feature\Alerts;

use App\Actions\Alerts\DispatchDailyDigestAction;
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

class DispatchDailyDigestActionTest extends TestCase
{
    use RefreshDatabase;

    protected Member $owner;

    protected Organization $organization;

    protected Category $category;

    protected Role $memberRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->memberRole = Role::create([
            'name' => 'member', 'title' => 'Member', 'is_active' => true,
            'is_admin' => false, 'perm' => [],
        ]);

        $this->owner = Member::factory()->create([
            'is_active' => true, 'is_blocked' => false, 'role_id' => $this->memberRole->id,
        ]);

        $this->organization = Organization::factory()->create([
            'member_id' => $this->owner->id,
            'is_active' => true,
        ]);

        $this->category = Category::create([
            'name' => 'Diseño', 'scope' => 'JobListing', 'slug' => 'diseno', 'order' => 1,
        ]);
    }

    private function memberWithAlert(?int $categoryId, ?string $city, bool $memberActive = true): Member
    {
        $member = Member::factory()->create([
            'is_active' => $memberActive, 'is_blocked' => false, 'role_id' => $this->memberRole->id,
        ]);

        JobAlert::factory()->create([
            'member_id' => $member->id,
            'category_id' => $categoryId,
            'city' => $city,
            'frequency' => JobAlertFrequency::Daily->value,
            'active' => true,
        ]);

        return $member;
    }

    public function test_sends_mail_for_alerts_with_matches_and_suppresses_others(): void
    {
        Mail::fake();
        Carbon::setTestNow('2026-05-12 09:00:00');

        // 3 members with matching alerts, 2 without.
        $matching = [];
        for ($i = 0; $i < 3; $i++) {
            $matching[] = $this->memberWithAlert($this->category->id, 'Lima');
        }
        $this->memberWithAlert($this->category->id, 'Trujillo');
        $this->memberWithAlert($this->category->id, 'Arequipa');

        // Create one matching offer for Lima.
        $offer = JobListing::factory()->forOrganization($this->organization)->active()->create([
            'city' => 'Lima',
            'application_deadline' => now()->addMonth(),
            'published_at' => now()->subHours(6),
        ]);
        $offer->categories()->attach($this->category);

        $summary = DispatchDailyDigestAction::run();

        $this->assertSame(5, $summary['processed']);
        $this->assertSame(3, $summary['sent']);
        $this->assertSame(2, $summary['suppressed_no_match']);

        Mail::assertQueuedCount(3);

        Carbon::setTestNow();
    }

    public function test_does_not_iterate_alerts_for_inactive_members(): void
    {
        Mail::fake();
        Carbon::setTestNow('2026-05-12 09:00:00');

        $this->memberWithAlert(null, null, memberActive: true);
        $this->memberWithAlert(null, null, memberActive: false);

        $offer = JobListing::factory()->forOrganization($this->organization)->active()->create([
            'application_deadline' => now()->addMonth(),
            'published_at' => now()->subHour(),
        ]);

        $summary = DispatchDailyDigestAction::run();

        $this->assertSame(1, $summary['processed']);
        $this->assertSame(1, $summary['sent']);
        Mail::assertQueuedCount(1);

        Carbon::setTestNow();
    }

    public function test_emits_dispatch_activity_log_with_summary(): void
    {
        Mail::fake();
        Carbon::setTestNow('2026-05-12 09:00:00');

        $this->memberWithAlert(null, null);

        DispatchDailyDigestAction::run();

        $this->assertTrue(
            Activity::query()->where('event', 'job-alert.dispatch.daily')->exists()
        );

        Carbon::setTestNow();
    }
}
