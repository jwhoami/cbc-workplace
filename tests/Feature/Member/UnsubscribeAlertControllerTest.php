<?php

declare(strict_types=1);

namespace Tests\Feature\Member;

use App\Enums\JobAlertFrequency;
use App\Enums\PublicEventKind;
use App\Models\JobAlert;
use App\Models\Member;
use App\Models\PublicEvent;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class UnsubscribeAlertControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Member $member;

    protected JobAlert $alert;

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

        $this->alert = JobAlert::factory()->create([
            'member_id' => $this->member->id,
            'frequency' => JobAlertFrequency::Daily->value,
            'active' => true,
        ]);
    }

    private function signedUrl(int $memberId, int $alertId): string
    {
        return URL::signedRoute('alerts.unsubscribe', [
            'member' => $memberId,
            'alert' => $alertId,
        ]);
    }

    public function test_valid_signed_url_deactivates_alert(): void
    {
        $response = $this->get($this->signedUrl($this->member->id, $this->alert->id));

        $response->assertOk();
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertFalse($this->alert->fresh()->active);
    }

    public function test_second_click_is_idempotent(): void
    {
        $url = $this->signedUrl($this->member->id, $this->alert->id);

        $this->get($url)->assertOk();
        $this->get($url)->assertOk();

        $this->assertFalse($this->alert->fresh()->active);

        $events = PublicEvent::query()
            ->where('kind', PublicEventKind::AlertUnsubscribedViaLink->value)
            ->where('payload->alert_id', $this->alert->id)
            ->get();

        $this->assertCount(2, $events);
        $this->assertFalse($events[0]->payload['was_already_inactive']);
        $this->assertTrue($events[1]->payload['was_already_inactive']);
    }

    public function test_tampered_signature_returns_403(): void
    {
        $url = $this->signedUrl($this->member->id, $this->alert->id).'-tampered';

        $this->get($url)->assertStatus(403);
        $this->assertTrue($this->alert->fresh()->active);
    }

    public function test_mismatched_member_alert_renders_neutral_view(): void
    {
        $other = Member::factory()->create([
            'is_active' => true, 'is_blocked' => false, 'role_id' => $this->member->role_id,
        ]);

        $url = $this->signedUrl($other->id, $this->alert->id);

        $response = $this->get($url);
        $response->assertOk();
        $response->assertSeeText(__('mail/job-alert.unsubscribe.not_found_title'));
        $this->assertTrue($this->alert->fresh()->active);
    }

    public function test_response_is_accessible_and_records_activity(): void
    {
        $url = $this->signedUrl($this->member->id, $this->alert->id);

        $response = $this->get($url);
        $response->assertSee('<main', false);
        $response->assertSee('<h1', false);
        $response->assertSee('aria-live="polite"', false);

        $this->assertTrue(
            Activity::query()->where('event', 'job-alert.unsubscribe-via-link')->exists()
        );
    }
}
