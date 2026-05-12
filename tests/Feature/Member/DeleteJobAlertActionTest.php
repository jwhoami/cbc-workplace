<?php

declare(strict_types=1);

namespace Tests\Feature\Member;

use App\Actions\Member\DeleteJobAlertAction;
use App\Enums\DispatchDecision;
use App\Enums\JobAlertFrequency;
use App\Enums\PublicEventKind;
use App\Models\Category;
use App\Models\JobAlert;
use App\Models\JobAlertDispatchLog;
use App\Models\Member;
use App\Models\PublicEvent;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DeleteJobAlertActionTest extends TestCase
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
    }

    public function test_deletes_alert_and_emits_event_with_criteria(): void
    {
        $alert = JobAlert::factory()->create([
            'member_id' => $this->member->id,
            'category_id' => $this->category->id,
            'city' => 'Lima',
            'frequency' => JobAlertFrequency::Daily->value,
        ]);
        $alertId = $alert->id;

        DeleteJobAlertAction::run($alert);

        $this->assertDatabaseMissing('job_alerts', ['id' => $alertId]);

        $event = PublicEvent::query()
            ->where('kind', PublicEventKind::AlertDeleted->value)
            ->where('payload->alert_id', $alertId)
            ->first();

        $this->assertNotNull($event);
        $this->assertSame($this->category->id, $event->payload['category_id']);
        $this->assertSame('Lima', $event->payload['city']);
        $this->assertSame(JobAlertFrequency::Daily->value, $event->payload['frequency']);
    }

    public function test_cascade_removes_dispatch_logs(): void
    {
        $alert = JobAlert::factory()->create([
            'member_id' => $this->member->id,
            'frequency' => JobAlertFrequency::Daily->value,
        ]);

        JobAlertDispatchLog::create([
            'job_alert_id' => $alert->id,
            'window_key' => 'daily:2026-05-12',
            'decision' => DispatchDecision::Sent->value,
            'matched_offer_ids' => [1, 2],
            'correlation_id' => (string) Str::uuid(),
            'dispatched_at' => now(),
        ]);

        DeleteJobAlertAction::run($alert);

        $this->assertDatabaseMissing('job_alert_dispatch_logs', ['job_alert_id' => $alert->id]);
    }
}
