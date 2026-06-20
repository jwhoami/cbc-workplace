<?php

declare(strict_types=1);

namespace Tests\Feature\Alerts;

use App\Actions\Alerts\BuildDigestForAlertAction;
use App\Actions\Alerts\CoalesceInstantMatchAction;
use App\Actions\Alerts\DispatchInstantAlertAction;
use App\Enums\DispatchDecision;
use App\Enums\JobAlertFrequency;
use App\Mail\Member\JobAlertDigest;
use App\Mail\Member\JobAlertInstantBatch;
use App\Models\Category;
use App\Models\JobAlert;
use App\Models\JobAlertDispatchLog;
use App\Models\JobListing;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class DispatchInstantAlertActionTest extends TestCase
{
    use RefreshDatabase;

    protected Role $role;

    protected Organization $organization;

    protected Category $category;

    protected Member $member;

    protected JobAlert $alert;

    protected function setUp(): void
    {
        parent::setUp();

        $this->role = Role::create([
            'name' => 'member', 'title' => 'Member', 'is_active' => true,
            'is_admin' => false, 'perm' => [],
        ]);

        $owner = Member::factory()->create(['is_active' => true, 'role_id' => $this->role->id]);
        $this->organization = Organization::factory()->create([
            'member_id' => $owner->id,
            'is_active' => true,
        ]);
        $this->category = Category::create([
            'name' => 'Diseño', 'scope' => 'JobListing', 'slug' => 'diseno', 'order' => 1,
        ]);

        $this->member = Member::factory()->create(['is_active' => true, 'role_id' => $this->role->id]);
        $this->alert = JobAlert::factory()->instant()->create([
            'member_id' => $this->member->id,
            'category_id' => $this->category->id,
            'city' => 'Lima',
        ]);
    }

    private function matchingOffer(): JobListing
    {
        // Real-world ordering: admin approves offer (published_at = now()),
        // listener fires immediately, CoalesceInstantMatchAction opens window
        // (opens_at = now()). The DispatchInstantAlertAction window is
        // [opens_at, now()], which includes any offer with published_at <= opens_at.
        // Publish the offer *at* now() so it falls within the window after
        // microsecond drift between the helper call and the action.
        $offer = JobListing::factory()->forOrganization($this->organization)->active()->create([
            'city' => 'Lima',
            'application_deadline' => now()->addMonth(),
            'published_at' => now(),
        ]);
        $offer->categories()->attach($this->category);

        return $offer->fresh();
    }

    public function test_single_match_dispatches_one_email(): void
    {
        Mail::fake();
        Queue::fake();
        Carbon::setTestNow('2026-05-12 09:00:00');

        $offer = $this->matchingOffer();

        CoalesceInstantMatchAction::run($this->alert, $offer);

        // Simulate the delayed dispatch firing (still frozen at 09:00:00).
        Mail::fake(); // reset

        $windowKey = \Illuminate\Support\Facades\Cache::get('alert-window:'.$this->alert->id)['window_key'];
        $decision = DispatchInstantAlertAction::run($this->alert->id, $windowKey);

        $this->assertSame(DispatchDecision::Sent, $decision);
        Mail::assertQueuedCount(1);

        Carbon::setTestNow();
    }

    public function test_disabled_alert_results_in_no_mail(): void
    {
        Mail::fake();
        Queue::fake();

        $offer = $this->matchingOffer();
        CoalesceInstantMatchAction::run($this->alert, $offer);

        $this->alert->update(['active' => false]);
        $windowKey = \Illuminate\Support\Facades\Cache::get('alert-window:'.$this->alert->id)['window_key'];

        $decision = DispatchInstantAlertAction::run($this->alert->id, $windowKey);

        $this->assertSame(DispatchDecision::SuppressedNoMatch, $decision);
        Mail::assertNothingQueued();
    }

    public function test_duplicate_window_key_absorbed_silently(): void
    {
        Mail::fake();
        Queue::fake();

        $offer = $this->matchingOffer();
        CoalesceInstantMatchAction::run($this->alert, $offer);
        $windowKey = \Illuminate\Support\Facades\Cache::get('alert-window:'.$this->alert->id)['window_key'];

        // Pre-insert the dispatch log row to simulate "already sent."
        JobAlertDispatchLog::create([
            'job_alert_id' => $this->alert->id,
            'window_key' => $windowKey,
            'decision' => DispatchDecision::Sent->value,
            'matched_offer_ids' => [$offer->id],
            'correlation_id' => (string) \Illuminate\Support\Str::uuid(),
            'dispatched_at' => now(),
        ]);

        $decision = DispatchInstantAlertAction::run($this->alert->id, $windowKey);

        // Spec 008 T075 Finding 2 fix: dedup must return AlreadySent, not
        // Sent, so callers can distinguish "I dispatched" from "another
        // worker already dispatched" without double-counting.
        $this->assertSame(DispatchDecision::AlreadySent, $decision);
        $this->assertSame(1, JobAlertDispatchLog::query()->where('job_alert_id', $this->alert->id)->count());
        Mail::assertNothingQueued();
    }

    public function test_emits_activity_log_summary(): void
    {
        Mail::fake();
        Queue::fake();

        $offer = $this->matchingOffer();
        CoalesceInstantMatchAction::run($this->alert, $offer);
        $windowKey = \Illuminate\Support\Facades\Cache::get('alert-window:'.$this->alert->id)['window_key'];

        DispatchInstantAlertAction::run($this->alert->id, $windowKey);

        $this->assertTrue(
            Activity::query()->where('event', 'job-alert.dispatch.instant')->exists()
        );
    }

    public function test_cross_channel_non_deduplication_instant_and_daily(): void
    {
        Mail::fake();
        Queue::fake();
        Carbon::setTestNow('2026-05-12 09:00:00');

        // Member has BOTH an instant and a daily alert for same criteria.
        JobAlert::factory()->daily()->create([
            'member_id' => $this->member->id,
            'category_id' => $this->category->id,
            'city' => 'Lima',
        ]);

        $offer = $this->matchingOffer();

        // Instant path — frozen at 09:00:00.
        CoalesceInstantMatchAction::run($this->alert, $offer);
        $windowKey = \Illuminate\Support\Facades\Cache::get('alert-window:'.$this->alert->id)['window_key'];
        DispatchInstantAlertAction::run($this->alert->id, $windowKey);

        // Daily path — must NOT deduplicate against the instant send (FR-024).
        Carbon::setTestNow('2026-05-12 11:00:00');
        $dailyAlert = JobAlert::query()
            ->where('member_id', $this->member->id)
            ->where('frequency', JobAlertFrequency::Daily->value)
            ->first();

        BuildDigestForAlertAction::run($dailyAlert, now()->subDay(), now(), 'daily:'.now()->format('Y-m-d'));

        Mail::assertQueued(JobAlertInstantBatch::class);
        Mail::assertQueued(JobAlertDigest::class);

        Carbon::setTestNow();
    }
}
