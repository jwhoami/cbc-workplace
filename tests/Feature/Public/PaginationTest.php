<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Enums\WorkModality;
use App\Models\JobListing;
use App\Models\Member;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 7 — User Story 5: pagination preserves URL state, page-2 +
 * filtered-page-1 emit `noindex,follow` (FR-024 + FR-027), page>last
 * renders gracefully.
 *
 * Per `SearchPublicOffersAction`, perPage = 20.
 */
class PaginationTest extends TestCase
{
    use RefreshDatabase;

    private const PER_PAGE = 20;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function makeOrg(): Organization
    {
        $member = Member::factory()->create();

        return Organization::factory()->create([
            'member_id' => $member->id,
            'is_active' => true,
        ]);
    }

    private function seedActiveOffers(int $count, array $overrides = []): void
    {
        $org = $this->makeOrg();
        JobListing::factory()
            ->count($count)
            ->forOrganization($org)
            ->active()
            ->create(array_merge([
                'application_deadline' => now()->addDays(30),
            ], $overrides));
    }

    public function test_url_state_round_trip_across_pages(): void
    {
        $org = $this->makeOrg();

        // 25 remote offers (spans 2 pages), 5 on-site (filtered out).
        JobListing::factory()
            ->count(25)
            ->forOrganization($org)
            ->active()
            ->create([
                'title' => 'Oferta Remota Round Trip',
                'work_modality' => WorkModality::REMOTE,
                'application_deadline' => now()->addDays(30),
            ]);
        JobListing::factory()
            ->count(5)
            ->forOrganization($org)
            ->active()
            ->create([
                'title' => 'Oferta Presencial Round Trip',
                'work_modality' => WorkModality::ON_SITE,
                'application_deadline' => now()->addDays(30),
            ]);

        $page2Url = '/bolsa-de-trabajo?work_mode[]='.WorkModality::REMOTE->value.'&page=2';

        $response = $this->get($page2Url);

        $response->assertOk();

        // Filter still applied: page 2 of remote-only must show remote
        // offers and never the on-site offers.
        $response->assertSee('Oferta Remota Round Trip');
        $response->assertDontSee('Oferta Presencial Round Trip');

        // The query string round-trips: the active filter checkbox stays
        // checked when the URL is reopened (FR-013, FR-021).
        $body = $response->getContent();
        $pattern = '#name="work_mode\[\]"\s+value="'.WorkModality::REMOTE->value.'"\s+checked#s';
        $this->assertMatchesRegularExpression(
            $pattern,
            (string) $body,
            'The active work_mode filter checkbox must remain checked on page 2 (FR-021).'
        );
    }

    public function test_page_beyond_max_renders_empty_state(): void
    {
        $this->seedActiveOffers(3);

        $response = $this->get('/bolsa-de-trabajo?page=999');

        $response->assertOk();
        $response->assertSee(__('public.listing.empty.title'));
    }

    public function test_page_one_no_robots_noindex(): void
    {
        $this->seedActiveOffers(3);

        $response = $this->get('/bolsa-de-trabajo');

        $response->assertOk();
        $response->assertDontSee('<meta name="robots"', escape: false);
    }

    public function test_page_two_has_robots_noindex_follow(): void
    {
        $this->seedActiveOffers(self::PER_PAGE + 5);

        $response = $this->get('/bolsa-de-trabajo?page=2');

        $response->assertOk();
        $response->assertSee('<meta name="robots" content="noindex,follow">', escape: false);
    }

    public function test_filtered_page_one_has_robots_noindex_follow(): void
    {
        $org = $this->makeOrg();
        JobListing::factory()
            ->forOrganization($org)
            ->active()
            ->create([
                'work_modality' => WorkModality::REMOTE,
                'application_deadline' => now()->addDays(30),
            ]);

        $response = $this->get('/bolsa-de-trabajo?work_mode[]='.WorkModality::REMOTE->value);

        $response->assertOk();
        $response->assertSee('<meta name="robots" content="noindex,follow">', escape: false);
    }

    public function test_pagination_preserves_filter_qs(): void
    {
        $org = $this->makeOrg();
        JobListing::factory()
            ->count(self::PER_PAGE + 5)
            ->forOrganization($org)
            ->active()
            ->create([
                'work_modality' => WorkModality::REMOTE,
                'application_deadline' => now()->addDays(30),
            ]);

        $response = $this->get('/bolsa-de-trabajo?work_mode[]='.WorkModality::REMOTE->value);

        $response->assertOk();
        // `withQueryString()` on the paginator means page links carry the
        // active filter — required for round-trip URL state per FR-021.
        $response->assertSee('work_mode%5B0%5D='.WorkModality::REMOTE->value, escape: false);
        $response->assertSee('page=2', escape: false);
    }
}
