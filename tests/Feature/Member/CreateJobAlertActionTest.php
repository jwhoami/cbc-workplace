<?php

declare(strict_types=1);

namespace Tests\Feature\Member;

use App\Actions\Member\CreateJobAlertAction;
use App\Enums\JobAlertFrequency;
use App\Enums\PublicEventKind;
use App\Exceptions\AlertQuotaExceededException;
use App\Exceptions\DuplicateAlertException;
use App\Models\Category;
use App\Models\JobAlert;
use App\Models\Member;
use App\Models\PublicEvent;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CreateJobAlertActionTest extends TestCase
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

    public function test_creates_alert_with_all_criteria(): void
    {
        $alert = CreateJobAlertAction::run($this->member, [
            'category_id' => $this->category->id,
            'city' => 'Lima',
            'frequency' => JobAlertFrequency::Daily->value,
        ]);

        $this->assertInstanceOf(JobAlert::class, $alert);
        $this->assertSame($this->member->id, $alert->member_id);
        $this->assertSame('Lima', $alert->city);
        $this->assertSame('lima', $alert->city_folded);
        $this->assertTrue($alert->active);
        $this->assertSame(JobAlertFrequency::Daily, $alert->frequency);
    }

    public function test_creates_alert_with_null_category_and_null_city(): void
    {
        $alert = CreateJobAlertAction::run($this->member, [
            'category_id' => null,
            'city' => null,
            'frequency' => JobAlertFrequency::Weekly->value,
        ]);

        $this->assertNull($alert->category_id);
        $this->assertNull($alert->city);
        $this->assertNull($alert->city_folded);
    }

    public function test_rejects_invalid_category(): void
    {
        $bad = Category::create([
            'name' => 'Productos', 'scope' => 'Venture', 'slug' => 'productos', 'order' => 1,
        ]);

        $this->expectException(ValidationException::class);
        CreateJobAlertAction::run($this->member, [
            'category_id' => $bad->id,
            'frequency' => JobAlertFrequency::Daily->value,
        ]);
    }

    public function test_rejects_duplicate_criteria(): void
    {
        CreateJobAlertAction::run($this->member, [
            'category_id' => $this->category->id,
            'city' => 'Lima',
            'frequency' => JobAlertFrequency::Daily->value,
        ]);

        $this->expectException(DuplicateAlertException::class);
        CreateJobAlertAction::run($this->member, [
            'category_id' => $this->category->id,
            'city' => 'lima',
            'frequency' => JobAlertFrequency::Daily->value,
        ]);
    }

    public function test_rejects_eleventh_alert_when_at_quota(): void
    {
        for ($i = 0; $i < 10; $i++) {
            JobAlert::factory()->create([
                'member_id' => $this->member->id,
                'category_id' => null,
                'city' => "City{$i}",
                'frequency' => JobAlertFrequency::Daily->value,
            ]);
        }

        $this->expectException(AlertQuotaExceededException::class);
        CreateJobAlertAction::run($this->member, [
            'category_id' => $this->category->id,
            'city' => 'Lima',
            'frequency' => JobAlertFrequency::Daily->value,
        ]);
    }

    public function test_emits_alert_created_public_event_with_pii_boundary(): void
    {
        $alert = CreateJobAlertAction::run($this->member, [
            'category_id' => $this->category->id,
            'city' => 'Lima',
            'frequency' => JobAlertFrequency::Daily->value,
        ]);

        $event = PublicEvent::query()
            ->where('kind', PublicEventKind::AlertCreated->value)
            ->where('payload->alert_id', $alert->id)
            ->first();

        $this->assertNotNull($event);
        $this->assertEquals([
            'member_id' => $this->member->id,
            'alert_id' => $alert->id,
            'category_id' => $this->category->id,
            'city' => 'Lima',
            'frequency' => JobAlertFrequency::Daily->value,
        ], $event->payload);

        // FR-030c — PII boundary: never log email, name, ip.
        $this->assertArrayNotHasKey('email', $event->payload);
        $this->assertArrayNotHasKey('name', $event->payload);
        $this->assertArrayNotHasKey('ip', $event->payload);
    }

    public function test_appends_comment_to_alert_on_create(): void
    {
        $alert = CreateJobAlertAction::run($this->member, [
            'category_id' => $this->category->id,
            'city' => 'Lima',
            'frequency' => JobAlertFrequency::Daily->value,
        ]);

        $this->assertSame(1, $alert->comments()->count());
        $this->assertStringContainsString($this->member->name, $alert->comments()->first()->comment);
    }
}
