<?php

declare(strict_types=1);

namespace Tests\Feature\Member;

use App\Actions\Member\ToggleJobAlertAction;
use App\Enums\JobAlertFrequency;
use App\Enums\PublicEventKind;
use App\Models\JobAlert;
use App\Models\Member;
use App\Models\PublicEvent;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ToggleJobAlertActionTest extends TestCase
{
    use RefreshDatabase;

    protected Member $member;

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
    }

    public function test_active_to_inactive_to_active_sequence(): void
    {
        $alert = JobAlert::factory()->create([
            'member_id' => $this->member->id,
            'frequency' => JobAlertFrequency::Daily->value,
            'active' => true,
        ]);

        $first = ToggleJobAlertAction::run($alert);
        $this->assertFalse($first->active);

        $second = ToggleJobAlertAction::run($first);
        $this->assertTrue($second->active);

        $events = PublicEvent::query()
            ->where('kind', PublicEventKind::AlertToggled->value)
            ->where('payload->alert_id', $alert->id)
            ->orderBy('id')
            ->get();

        $this->assertCount(2, $events);
        $this->assertFalse($events[0]->payload['active']);
        $this->assertTrue($events[1]->payload['active']);
    }
}
