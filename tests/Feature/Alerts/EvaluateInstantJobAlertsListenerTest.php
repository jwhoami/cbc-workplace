<?php

declare(strict_types=1);

namespace Tests\Feature\Alerts;

use App\Actions\Alerts\CoalesceInstantMatchAction;
use App\Events\JobListingApproved;
use App\Listeners\EvaluateInstantJobAlerts;
use App\Models\Category;
use App\Models\JobAlert;
use App\Models\JobListing;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Lorisleiva\Actions\Decorators\JobDecorator;
use Tests\TestCase;

class EvaluateInstantJobAlertsListenerTest extends TestCase
{
    use RefreshDatabase;

    protected Role $role;

    protected Organization $organization;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->role = Role::create([
            'name' => 'member', 'title' => 'Member', 'is_active' => true,
            'is_admin' => false, 'perm' => [],
        ]);

        $owner = Member::factory()->create([
            'is_active' => true, 'role_id' => $this->role->id,
        ]);

        $this->organization = Organization::factory()->create([
            'member_id' => $owner->id,
            'is_active' => true,
        ]);

        $this->category = Category::create([
            'name' => 'Diseño', 'scope' => 'JobListing', 'slug' => 'diseno', 'order' => 1,
        ]);
    }

    private function activeJobListing(string $city = 'Lima'): JobListing
    {
        $offer = JobListing::factory()->forOrganization($this->organization)->active()->create([
            'city' => $city,
            'application_deadline' => now()->addMonth(),
            'published_at' => now()->subMinutes(2),
        ]);
        $offer->categories()->attach($this->category);

        return $offer->fresh();
    }

    public function test_dispatches_one_coalesce_per_matching_alert(): void
    {
        Queue::fake();

        $matchingMember = Member::factory()->create(['is_active' => true, 'role_id' => $this->role->id]);
        JobAlert::factory()->instant()->create([
            'member_id' => $matchingMember->id,
            'category_id' => $this->category->id,
            'city' => 'Lima',
        ]);

        $offer = $this->activeJobListing('Lima');

        (new EvaluateInstantJobAlerts)->handle(new JobListingApproved($offer));

        // AsJob wraps the action in a JobDecorator before queueing.
        Queue::assertPushed(JobDecorator::class, function (JobDecorator $job) {
            return $job->getAction() instanceof CoalesceInstantMatchAction
                && $job->queue === 'instant';
        });
    }

    public function test_no_jobs_for_non_matching_criteria(): void
    {
        Queue::fake();

        $member = Member::factory()->create(['is_active' => true, 'role_id' => $this->role->id]);
        JobAlert::factory()->instant()->create([
            'member_id' => $member->id,
            'category_id' => $this->category->id,
            'city' => 'Cusco',
        ]);

        $offer = $this->activeJobListing('Lima');
        (new EvaluateInstantJobAlerts)->handle(new JobListingApproved($offer));

        Queue::assertNothingPushed();
    }

    public function test_no_jobs_for_non_instant_alerts(): void
    {
        Queue::fake();

        $member = Member::factory()->create(['is_active' => true, 'role_id' => $this->role->id]);
        JobAlert::factory()->daily()->create([
            'member_id' => $member->id,
            'category_id' => $this->category->id,
            'city' => 'Lima',
        ]);

        $offer = $this->activeJobListing('Lima');
        (new EvaluateInstantJobAlerts)->handle(new JobListingApproved($offer));

        Queue::assertNothingPushed();
    }

    public function test_no_jobs_for_inactive_members(): void
    {
        Queue::fake();

        $inactive = Member::factory()->create(['is_active' => false, 'role_id' => $this->role->id]);
        JobAlert::factory()->instant()->create([
            'member_id' => $inactive->id,
            'category_id' => $this->category->id,
            'city' => 'Lima',
        ]);

        $offer = $this->activeJobListing('Lima');
        (new EvaluateInstantJobAlerts)->handle(new JobListingApproved($offer));

        Queue::assertNothingPushed();
    }
}
