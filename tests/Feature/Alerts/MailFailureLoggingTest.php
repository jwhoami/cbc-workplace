<?php

declare(strict_types=1);

namespace Tests\Feature\Alerts;

use App\Enums\JobAlertFrequency;
use App\Mail\Member\JobAlertDigest;
use App\Models\Category;
use App\Models\JobAlert;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class MailFailureLoggingTest extends TestCase
{
    use RefreshDatabase;

    public function test_failed_callback_logs_diagnostic_without_pii(): void
    {
        $role = Role::create([
            'name' => 'member', 'title' => 'Member', 'is_active' => true,
            'is_admin' => false, 'perm' => [],
        ]);
        $owner = Member::factory()->create(['is_active' => true, 'role_id' => $role->id]);
        Organization::factory()->create(['member_id' => $owner->id, 'is_active' => true]);
        $cat = Category::create(['name' => 'Diseño', 'scope' => 'JobListing', 'slug' => 'diseno', 'order' => 1]);

        $member = Member::factory()->create([
            'is_active' => true,
            'role_id' => $role->id,
            'name' => 'Confidential Person',
            'email' => 'private@example.test',
        ]);

        $alert = JobAlert::factory()->create([
            'member_id' => $member->id,
            'category_id' => $cat->id,
            'frequency' => JobAlertFrequency::Daily->value,
        ]);

        $mailable = new JobAlertDigest($alert, new Collection, JobAlertFrequency::Daily);
        $mailable->failed(new \RuntimeException('smtp down'));

        $activity = Activity::query()->where('event', 'job-alert.dispatch.failed')->first();
        $this->assertNotNull($activity);
        $props = $activity->properties->toArray();

        $this->assertArrayNotHasKey('email', $props);
        $this->assertArrayNotHasKey('name', $props);
        $this->assertArrayHasKey('alert_id', $props);
        $this->assertArrayHasKey('reason', $props);
        $this->assertSame('smtp down', $props['reason']);
    }
}
