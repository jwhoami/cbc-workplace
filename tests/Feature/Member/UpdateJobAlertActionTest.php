<?php

declare(strict_types=1);

namespace Tests\Feature\Member;

use App\Actions\Member\UpdateJobAlertAction;
use App\Enums\JobAlertFrequency;
use App\Enums\PublicEventKind;
use App\Exceptions\DuplicateAlertException;
use App\Models\Category;
use App\Models\JobAlert;
use App\Models\Member;
use App\Models\PublicEvent;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateJobAlertActionTest extends TestCase
{
    use RefreshDatabase;

    protected Member $member;

    protected Category $categoryA;

    protected Category $categoryB;

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

        $this->categoryA = Category::create(['name' => 'Diseño', 'scope' => 'JobListing', 'slug' => 'diseno', 'order' => 1]);
        $this->categoryB = Category::create(['name' => 'Marketing', 'scope' => 'JobListing', 'slug' => 'marketing', 'order' => 2]);
    }

    public function test_updates_category_field(): void
    {
        $alert = JobAlert::factory()->create([
            'member_id' => $this->member->id,
            'category_id' => $this->categoryA->id,
            'city' => 'Lima',
            'frequency' => JobAlertFrequency::Daily->value,
        ]);

        $updated = UpdateJobAlertAction::run($alert, [
            'category_id' => $this->categoryB->id,
            'city' => 'Lima',
            'frequency' => JobAlertFrequency::Daily->value,
        ]);

        $this->assertSame($this->categoryB->id, $updated->category_id);
    }

    public function test_updates_city_and_recomputes_folded(): void
    {
        $alert = JobAlert::factory()->create([
            'member_id' => $this->member->id,
            'category_id' => $this->categoryA->id,
            'city' => 'Lima',
            'frequency' => JobAlertFrequency::Daily->value,
        ]);

        $updated = UpdateJobAlertAction::run($alert, [
            'category_id' => $this->categoryA->id,
            'city' => 'Trujíllo',
            'frequency' => JobAlertFrequency::Daily->value,
        ]);

        $this->assertSame('Trujíllo', $updated->city);
        $this->assertSame('trujillo', $updated->city_folded);
    }

    public function test_updates_frequency(): void
    {
        $alert = JobAlert::factory()->create([
            'member_id' => $this->member->id,
            'category_id' => $this->categoryA->id,
            'city' => 'Lima',
            'frequency' => JobAlertFrequency::Daily->value,
        ]);

        $updated = UpdateJobAlertAction::run($alert, [
            'category_id' => $this->categoryA->id,
            'city' => 'Lima',
            'frequency' => JobAlertFrequency::Weekly->value,
        ]);

        $this->assertSame(JobAlertFrequency::Weekly, $updated->frequency);
    }

    public function test_rejects_clash_with_another_alert(): void
    {
        $a = JobAlert::factory()->create([
            'member_id' => $this->member->id,
            'category_id' => $this->categoryA->id,
            'city' => 'Lima',
            'frequency' => JobAlertFrequency::Daily->value,
        ]);

        $b = JobAlert::factory()->create([
            'member_id' => $this->member->id,
            'category_id' => $this->categoryB->id,
            'city' => 'Lima',
            'frequency' => JobAlertFrequency::Daily->value,
        ]);

        $this->expectException(DuplicateAlertException::class);
        UpdateJobAlertAction::run($b, [
            'category_id' => $this->categoryA->id,
            'city' => 'Lima',
            'frequency' => JobAlertFrequency::Daily->value,
        ]);
    }

    public function test_emits_alert_edited_event_with_changed_payload(): void
    {
        $alert = JobAlert::factory()->create([
            'member_id' => $this->member->id,
            'category_id' => $this->categoryA->id,
            'city' => 'Lima',
            'frequency' => JobAlertFrequency::Daily->value,
        ]);

        UpdateJobAlertAction::run($alert, [
            'category_id' => $this->categoryB->id,
            'city' => 'Trujillo',
            'frequency' => JobAlertFrequency::Weekly->value,
        ]);

        $event = PublicEvent::query()
            ->where('kind', PublicEventKind::AlertEdited->value)
            ->where('payload->alert_id', $alert->id)
            ->first();

        $this->assertNotNull($event);
        // city_folded is computed by JobAlertObserver::saving() which fires
        // AFTER getDirty() is captured in the action, so it's not in the
        // changed list. Member-visible fields only.
        $this->assertEqualsCanonicalizing(
            ['category_id', 'city', 'frequency'],
            $event->payload['changed']
        );
    }
}
