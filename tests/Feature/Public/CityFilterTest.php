<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Actions\Public\ResolveCityFilterAction;
use App\Models\JobListing;
use App\Models\Member;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CityFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Cache::forget(ResolveCityFilterAction::CACHE_KEY);
    }

    private function makeOrg(): Organization
    {
        $member = Member::factory()->create();

        return Organization::factory()->create([
            'member_id' => $member->id,
            'is_active' => true,
        ]);
    }

    public function test_dropdown_lists_distinct_cities_of_active_offers(): void
    {
        $org = $this->makeOrg();

        JobListing::factory()->forOrganization($org)->active()->create([
            'city' => 'Ciudad de Panamá',
            'application_deadline' => now()->addDays(20),
        ]);
        JobListing::factory()->forOrganization($org)->active()->create([
            'city' => 'David',
            'application_deadline' => now()->addDays(20),
        ]);
        // Duplicate city — should appear once.
        JobListing::factory()->forOrganization($org)->active()->create([
            'city' => 'Ciudad de Panamá',
            'application_deadline' => now()->addDays(20),
        ]);

        $cities = ResolveCityFilterAction::run();

        $this->assertSame(['Ciudad de Panamá', 'David'], $cities);
    }

    public function test_dropdown_excludes_inactive_or_expired_offers_cities(): void
    {
        $org = $this->makeOrg();

        JobListing::factory()->forOrganization($org)->active()->create([
            'city' => 'San José',
            'application_deadline' => now()->addDays(20),
        ]);
        JobListing::factory()->forOrganization($org)->expired()->create([
            'city' => 'Solo Expirada',
        ]);
        JobListing::factory()->forOrganization($org)->draft()->create([
            'city' => 'Solo Draft',
        ]);

        $cities = ResolveCityFilterAction::run();

        $this->assertContains('San José', $cities);
        $this->assertNotContains('Solo Expirada', $cities);
        $this->assertNotContains('Solo Draft', $cities);
    }

    public function test_dropdown_excludes_cities_from_hidden_orgs(): void
    {
        $hiddenOrg = Organization::factory()->create([
            'member_id' => Member::factory()->create()->id,
            'is_active' => false,
        ]);

        JobListing::factory()->forOrganization($hiddenOrg)->active()->create([
            'city' => 'Ciudad Oculta',
            'application_deadline' => now()->addDays(20),
        ]);

        $cities = ResolveCityFilterAction::run();

        $this->assertNotContains('Ciudad Oculta', $cities);
    }

    public function test_observer_busts_cache_when_offer_state_changes(): void
    {
        $org = $this->makeOrg();

        $listing = JobListing::factory()->forOrganization($org)->active()->create([
            'city' => 'Pre-Cache City',
            'application_deadline' => now()->addDays(20),
        ]);

        // Prime cache
        $first = ResolveCityFilterAction::run();
        $this->assertContains('Pre-Cache City', $first);

        // Mutate state to EXPIRED → observer should bust the cache.
        // Note: state is protected from mass assignment per spec 005's
        // MassAssignmentTest, so we set it directly + save() rather than
        // calling update().
        $listing->state = \App\Enums\JobListingState::EXPIRED;
        $listing->save();

        $afterBust = ResolveCityFilterAction::run();
        $this->assertNotContains('Pre-Cache City', $afterBust);
    }

    public function test_stale_url_with_disappeared_city_renders_empty_state(): void
    {
        $org = $this->makeOrg();

        JobListing::factory()->forOrganization($org)->active()->create([
            'city' => 'San José',
            'application_deadline' => now()->addDays(20),
        ]);

        // Visitor lands with a stale URL pointing to a city no longer in the dropdown.
        $response = $this->get('/bolsa-de-trabajo?city[]=Ciudad%20Inexistente');

        $response->assertOk();
        $response->assertSee(__('public.listing.empty.with_filters.title'));
    }

    public function test_listing_does_not_block_first_paint_on_cache_miss(): void
    {
        // FR-010c: city filter population MUST NOT block first paint.
        // We can't measure timing in a unit test, but we can assert the
        // listing renders 200 even with cache cold.
        Cache::forget(ResolveCityFilterAction::CACHE_KEY);

        $org = $this->makeOrg();
        JobListing::factory()->forOrganization($org)->active()->create([
            'city' => 'Test City',
            'application_deadline' => now()->addDays(20),
        ]);

        $response = $this->get('/bolsa-de-trabajo');

        $response->assertOk();
    }
}
