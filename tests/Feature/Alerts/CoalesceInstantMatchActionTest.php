<?php

declare(strict_types=1);

namespace Tests\Feature\Alerts;

use App\Actions\Alerts\CoalesceInstantMatchAction;
use App\Actions\Alerts\DispatchInstantAlertAction;
use App\Models\Category;
use App\Models\JobAlert;
use App\Models\JobListing;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Lorisleiva\Actions\Decorators\JobDecorator;
use Tests\TestCase;

class CoalesceInstantMatchActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_opens_a_new_window_and_dispatches_delayed_job(): void
    {
        Queue::fake();

        $role = Role::create([
            'name' => 'member', 'title' => 'Member', 'is_active' => true,
            'is_admin' => false, 'perm' => [],
        ]);
        $owner = Member::factory()->create(['is_active' => true, 'role_id' => $role->id]);
        $org = Organization::factory()->create(['member_id' => $owner->id, 'is_active' => true]);
        $cat = Category::create(['name' => 'Diseño', 'scope' => 'JobListing', 'slug' => 'diseno', 'order' => 1]);

        $member = Member::factory()->create(['is_active' => true, 'role_id' => $role->id]);
        $alert = JobAlert::factory()->instant()->create([
            'member_id' => $member->id,
            'category_id' => $cat->id,
            'city' => 'Lima',
        ]);

        $offer = JobListing::factory()->forOrganization($org)->active()->create([
            'city' => 'Lima',
            'application_deadline' => now()->addMonth(),
            'published_at' => now()->subMinute(),
        ]);

        CoalesceInstantMatchAction::run($alert, $offer);

        $cached = Cache::get('alert-window:'.$alert->id);
        $this->assertNotNull($cached);
        $this->assertSame([$offer->id], $cached['offer_ids']);
        $this->assertStringStartsWith('instant:', $cached['window_key']);

        // AsJob wraps DispatchInstantAlertAction in a JobDecorator.
        Queue::assertPushed(JobDecorator::class, function (JobDecorator $job) {
            return $job->getAction() instanceof DispatchInstantAlertAction;
        });
    }

    public function test_extends_existing_window_with_additional_offer(): void
    {
        Queue::fake();

        $role = Role::create([
            'name' => 'member', 'title' => 'Member', 'is_active' => true,
            'is_admin' => false, 'perm' => [],
        ]);
        $owner = Member::factory()->create(['is_active' => true, 'role_id' => $role->id]);
        $org = Organization::factory()->create(['member_id' => $owner->id, 'is_active' => true]);
        $cat = Category::create(['name' => 'Diseño', 'scope' => 'JobListing', 'slug' => 'diseno', 'order' => 1]);

        $member = Member::factory()->create(['is_active' => true, 'role_id' => $role->id]);
        $alert = JobAlert::factory()->instant()->create([
            'member_id' => $member->id, 'category_id' => $cat->id, 'city' => 'Lima',
        ]);

        $offer1 = JobListing::factory()->forOrganization($org)->active()->create([
            'city' => 'Lima', 'application_deadline' => now()->addMonth(), 'published_at' => now(),
        ]);
        $offer2 = JobListing::factory()->forOrganization($org)->active()->create([
            'city' => 'Lima', 'application_deadline' => now()->addMonth(), 'published_at' => now(),
        ]);

        CoalesceInstantMatchAction::run($alert, $offer1);
        CoalesceInstantMatchAction::run($alert, $offer2);

        $cached = Cache::get('alert-window:'.$alert->id);
        $this->assertSame([$offer1->id, $offer2->id], $cached['offer_ids']);

        // Only one delayed DispatchInstantAlertAction should have been queued
        // (on window-open). AsJob wraps the action in JobDecorator.
        $pushed = Queue::pushed(JobDecorator::class, function (JobDecorator $job) {
            return $job->getAction() instanceof DispatchInstantAlertAction;
        });
        $this->assertCount(1, $pushed);
    }
}
