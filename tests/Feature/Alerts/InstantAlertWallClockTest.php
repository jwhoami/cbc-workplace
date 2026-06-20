<?php

declare(strict_types=1);

namespace Tests\Feature\Alerts;

use App\Actions\Alerts\CoalesceInstantMatchAction;
use App\Actions\Alerts\DispatchInstantAlertAction;
use App\Enums\DispatchDecision;
use App\Enums\JobAlertFrequency;
use App\Mail\Member\JobAlertInstantBatch;
use App\Models\Category;
use App\Models\JobAlert;
use App\Models\JobListing;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Regression test for the production bug surfaced by T075 manual verification:
 *
 * The triggering offer's `published_at` is set inside JobListingApproval::approve()
 * BEFORE the JobListingApproved event fires. The listener queues
 * CoalesceInstantMatchAction which sets `opens_at = now()`. The result is
 * `published_at < opens_at` by a few milliseconds-to-seconds (queue lag).
 *
 * Without a lookback grace in DispatchInstantAlertAction, the re-validation
 * window `[opens_at, now()]` excludes the triggering offer and the alert is
 * suppressed as `no_match` — meaning instant alerts effectively never fire in
 * production.
 *
 * This test does NOT use Carbon::setTestNow; it sets `published_at` 5 seconds
 * before `now()` to cross MySQL's 1-second TIMESTAMP precision boundary
 * reliably. If the lookback grace is removed, this test fails as
 * `SuppressedNoMatch`.
 */
class InstantAlertWallClockTest extends TestCase
{
    use RefreshDatabase;

    public function test_offer_published_before_window_open_still_matches_via_lookback_grace(): void
    {
        Mail::fake();
        Queue::fake();

        $role = Role::create([
            'name' => 'member', 'title' => 'Member', 'is_active' => true,
            'is_admin' => false, 'perm' => [],
        ]);
        $owner = Member::factory()->create(['is_active' => true, 'role_id' => $role->id]);
        $org = Organization::factory()->create(['member_id' => $owner->id, 'is_active' => true]);
        $cat = Category::create([
            'name' => 'Diseño', 'scope' => 'JobListing', 'slug' => 'diseno', 'order' => 1,
        ]);

        $member = Member::factory()->create(['is_active' => true, 'role_id' => $role->id]);
        $alert = JobAlert::factory()->create([
            'member_id' => $member->id,
            'category_id' => $cat->id,
            'city' => 'Lima',
            'frequency' => JobAlertFrequency::Instant->value,
            'active' => true,
        ]);

        // Mimic the real production timeline: offer is published FIRST
        // (admin clicks Approve → JobListingApproval::approve() sets
        // published_at), then the listener fires after queue lag. MySQL
        // TIMESTAMP has 1-second precision, so we set published_at 5
        // seconds in the past to make the ordering visible across the
        // MySQL boundary — well outside test jitter and well inside the
        // 60s lookback grace.
        $offer = JobListing::factory()->forOrganization($org)->active()->create([
            'city' => 'Lima',
            'application_deadline' => now()->addMonth(),
            'published_at' => now()->subSeconds(5),
        ]);
        $offer->categories()->attach($cat);

        // Now coalesce — opens_at = now(), strictly after published_at.
        CoalesceInstantMatchAction::run($alert, $offer);

        $windowKey = Cache::get('alert-window:'.$alert->id)['window_key'];
        $decision = DispatchInstantAlertAction::run($alert->id, $windowKey);

        // Without the lookback grace this asserts SuppressedNoMatch.
        $this->assertSame(DispatchDecision::Sent, $decision);
        Mail::assertQueued(JobAlertInstantBatch::class);
    }
}
