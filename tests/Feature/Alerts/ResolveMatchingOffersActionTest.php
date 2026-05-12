<?php

declare(strict_types=1);

namespace Tests\Feature\Alerts;

use App\Actions\Alerts\ResolveMatchingOffersAction;
use App\Enums\JobAlertFrequency;
use App\Models\Category;
use App\Models\JobAlert;
use App\Models\JobListing;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResolveMatchingOffersActionTest extends TestCase
{
    use RefreshDatabase;

    protected Member $member;

    protected Member $orgOwner;

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
        $this->orgOwner = Member::factory()->create([
            'is_active' => true, 'is_blocked' => false, 'role_id' => $role->id,
        ]);
        $this->organization = Organization::factory()->create([
            'member_id' => $this->orgOwner->id,
            'is_active' => true,
        ]);
        $this->category = Category::create([
            'name' => 'Diseño', 'scope' => 'JobListing', 'slug' => 'diseno', 'order' => 1,
        ]);
    }

    public function test_matches_only_active_offers_within_window(): void
    {
        $alert = JobAlert::factory()->create([
            'member_id' => $this->member->id,
            'category_id' => $this->category->id,
            'city' => 'Lima',
            'frequency' => JobAlertFrequency::Daily->value,
        ]);

        $match = JobListing::factory()->forOrganization($this->organization)->active()->create([
            'city' => 'Lima',
            'application_deadline' => now()->addMonth(),
            'published_at' => now()->subHours(6),
        ]);
        $match->categories()->attach($this->category);

        $outsideWindow = JobListing::factory()->forOrganization($this->organization)->active()->create([
            'city' => 'Lima',
            'application_deadline' => now()->addMonth(),
            'published_at' => now()->subDays(5),
        ]);
        $outsideWindow->categories()->attach($this->category);

        $matches = ResolveMatchingOffersAction::run($alert, now()->subDay(), now());

        $this->assertCount(1, $matches);
        $this->assertSame($match->id, $matches->first()->id);
    }

    public function test_null_category_matches_any_category(): void
    {
        $alert = JobAlert::factory()->create([
            'member_id' => $this->member->id,
            'category_id' => null,
            'city' => 'Lima',
            'frequency' => JobAlertFrequency::Daily->value,
        ]);

        $offer = JobListing::factory()->forOrganization($this->organization)->active()->create([
            'city' => 'Lima',
            'application_deadline' => now()->addMonth(),
            'published_at' => now()->subHours(2),
        ]);
        $offer->categories()->attach($this->category);

        $matches = ResolveMatchingOffersAction::run($alert, now()->subDay(), now());
        $this->assertCount(1, $matches);
    }

    public function test_accent_insensitive_city_match(): void
    {
        $alert = JobAlert::factory()->create([
            'member_id' => $this->member->id,
            'category_id' => null,
            'city' => 'Trujíllo',
            'frequency' => JobAlertFrequency::Daily->value,
        ]);

        $offer = JobListing::factory()->forOrganization($this->organization)->active()->create([
            'city' => 'trujillo',
            'application_deadline' => now()->addMonth(),
            'published_at' => now()->subHours(2),
        ]);

        $matches = ResolveMatchingOffersAction::run($alert, now()->subDay(), now());
        $this->assertCount(1, $matches);
        $this->assertSame($offer->id, $matches->first()->id);
    }

    public function test_excludes_offers_from_inactive_organizations(): void
    {
        $inactiveOwner = Member::factory()->create([
            'is_active' => true, 'is_blocked' => false, 'role_id' => $this->member->role_id,
        ]);
        $inactiveOrg = Organization::factory()->create([
            'member_id' => $inactiveOwner->id,
            'is_active' => false,
        ]);

        $alert = JobAlert::factory()->create([
            'member_id' => $this->member->id,
            'category_id' => null,
            'city' => null,
            'frequency' => JobAlertFrequency::Daily->value,
        ]);

        JobListing::factory()->forOrganization($inactiveOrg)->active()->create([
            'application_deadline' => now()->addMonth(),
            'published_at' => now()->subHour(),
        ]);

        $matches = ResolveMatchingOffersAction::run($alert, now()->subDay(), now());
        $this->assertCount(0, $matches);
    }

    public function test_excludes_expired_offers(): void
    {
        $alert = JobAlert::factory()->create([
            'member_id' => $this->member->id,
            'category_id' => null,
            'city' => null,
            'frequency' => JobAlertFrequency::Daily->value,
        ]);

        JobListing::factory()->forOrganization($this->organization)->active()->create([
            'application_deadline' => now()->subDay(),
            'published_at' => now()->subHour(),
        ]);

        $matches = ResolveMatchingOffersAction::run($alert, now()->subDay(), now());
        $this->assertCount(0, $matches);
    }
}
