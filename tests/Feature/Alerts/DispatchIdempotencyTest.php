<?php

declare(strict_types=1);

namespace Tests\Feature\Alerts;

use App\Actions\Alerts\DispatchDailyDigestAction;
use App\Enums\JobAlertFrequency;
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
use Tests\TestCase;

class DispatchIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_second_run_in_same_window_produces_no_new_mail(): void
    {
        Mail::fake();
        Carbon::setTestNow('2026-05-12 09:00:00');

        $role = Role::create([
            'name' => 'member', 'title' => 'Member', 'is_active' => true,
            'is_admin' => false, 'perm' => [],
        ]);
        $owner = Member::factory()->create(['is_active' => true, 'role_id' => $role->id]);
        $org = Organization::factory()->create(['member_id' => $owner->id, 'is_active' => true]);
        $cat = Category::create(['name' => 'Diseño', 'scope' => 'JobListing', 'slug' => 'diseno', 'order' => 1]);

        $member = Member::factory()->create(['is_active' => true, 'role_id' => $role->id]);
        JobAlert::factory()->create([
            'member_id' => $member->id,
            'category_id' => $cat->id,
            'city' => null,
            'frequency' => JobAlertFrequency::Daily->value,
        ]);

        $offer = JobListing::factory()->forOrganization($org)->active()->create([
            'application_deadline' => now()->addMonth(),
            'published_at' => now()->subHour(),
        ]);
        $offer->categories()->attach($cat);

        DispatchDailyDigestAction::run();
        $countAfterFirst = JobAlertDispatchLog::query()->count();
        DispatchDailyDigestAction::run();
        $countAfterSecond = JobAlertDispatchLog::query()->count();

        Mail::assertQueuedCount(1);
        $this->assertSame($countAfterFirst, $countAfterSecond);

        Carbon::setTestNow();
    }
}
