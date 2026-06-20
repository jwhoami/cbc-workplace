<?php

declare(strict_types=1);

namespace Tests\Feature\Alerts;

use App\Actions\Alerts\BuildDigestForAlertAction;
use App\Enums\DispatchDecision;
use App\Enums\JobAlertFrequency;
use App\Enums\PublicEventKind;
use App\Mail\Member\JobAlertDigest;
use App\Models\Category;
use App\Models\JobAlert;
use App\Models\JobAlertDispatchLog;
use App\Models\JobListing;
use App\Models\Member;
use App\Models\Organization;
use App\Models\PublicEvent;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BuildDigestForAlertActionTest extends TestCase
{
    use RefreshDatabase;

    protected Member $member;

    protected Organization $organization;

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

        $owner = Member::factory()->create([
            'is_active' => true, 'is_blocked' => false, 'role_id' => $role->id,
        ]);

        $this->organization = Organization::factory()->create([
            'member_id' => $owner->id,
            'is_active' => true,
        ]);

        $this->category = Category::create([
            'name' => 'Diseño', 'scope' => 'JobListing', 'slug' => 'diseno', 'order' => 1,
        ]);
    }

    public function test_zero_match_records_suppression_and_emits_event_without_mail(): void
    {
        Mail::fake();

        $alert = JobAlert::factory()->create([
            'member_id' => $this->member->id,
            'category_id' => $this->category->id,
            'city' => 'Lima',
            'frequency' => JobAlertFrequency::Daily->value,
        ]);

        $decision = BuildDigestForAlertAction::run($alert, now()->subDay(), now(), 'daily:2026-05-12');

        $this->assertSame(DispatchDecision::SuppressedNoMatch, $decision);
        Mail::assertNothingQueued();

        $this->assertDatabaseHas('job_alert_dispatch_logs', [
            'job_alert_id' => $alert->id,
            'window_key' => 'daily:2026-05-12',
            'decision' => DispatchDecision::SuppressedNoMatch->value,
        ]);

        $this->assertTrue(
            PublicEvent::query()
                ->where('kind', PublicEventKind::AlertEmailSuppressedNoMatch->value)
                ->where('payload->alert_id', $alert->id)
                ->exists()
        );
    }

    public function test_happy_path_queues_mail_and_emits_sent_event_with_shared_correlation_id(): void
    {
        Mail::fake();

        $alert = JobAlert::factory()->create([
            'member_id' => $this->member->id,
            'category_id' => $this->category->id,
            'city' => 'Lima',
            'frequency' => JobAlertFrequency::Daily->value,
        ]);

        $offer = JobListing::factory()->forOrganization($this->organization)->active()->create([
            'city' => 'Lima',
            'application_deadline' => now()->addMonth(),
            'published_at' => now()->subHours(2),
        ]);
        $offer->categories()->attach($this->category);

        $decision = BuildDigestForAlertAction::run($alert, now()->subDay(), now(), 'daily:2026-05-12');

        $this->assertSame(DispatchDecision::Sent, $decision);
        Mail::assertQueued(JobAlertDigest::class);

        $log = JobAlertDispatchLog::query()->where('job_alert_id', $alert->id)->first();
        $this->assertNotNull($log);

        $event = PublicEvent::query()
            ->where('kind', PublicEventKind::AlertEmailSent->value)
            ->where('payload->alert_id', $alert->id)
            ->first();

        $this->assertNotNull($event);
        $this->assertSame($log->correlation_id, $event->correlation_id);
        $this->assertSame(1, $event->payload['offer_count']);
    }

    public function test_duplicate_window_key_call_is_noop(): void
    {
        Mail::fake();

        $alert = JobAlert::factory()->create([
            'member_id' => $this->member->id,
            'category_id' => null,
            'city' => null,
            'frequency' => JobAlertFrequency::Daily->value,
        ]);

        $offer = JobListing::factory()->forOrganization($this->organization)->active()->create([
            'application_deadline' => now()->addMonth(),
            'published_at' => now()->subHour(),
        ]);

        $first = BuildDigestForAlertAction::run($alert, now()->subDay(), now(), 'daily:2026-05-12');
        $second = BuildDigestForAlertAction::run($alert, now()->subDay(), now(), 'daily:2026-05-12');

        // First call queues the mail and writes the row → Sent.
        $this->assertSame(DispatchDecision::Sent, $first);
        // Second call hits the unique constraint → distinct AlreadySent
        // (spec 008 T075 Finding 2 fix). Does NOT increment a `sent` bucket.
        $this->assertSame(DispatchDecision::AlreadySent, $second);

        Mail::assertQueuedCount(1);
        $this->assertSame(1, JobAlertDispatchLog::query()->where('job_alert_id', $alert->id)->count());
    }
}
