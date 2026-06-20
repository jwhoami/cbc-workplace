<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Models\JobListing;
use App\Models\Member;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 8 — accessibility (FR-032). The Lighthouse run (T123) is the
 * end-to-end signal; these tests pin the structural guarantees that
 * Lighthouse depends on so a future template edit can't silently break
 * the audit.
 */
class AccessibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function makeActiveOffer(array $listingAttrs = []): JobListing
    {
        $member = Member::factory()->create();
        $org = Organization::factory()->create([
            'member_id' => $member->id,
            'is_active' => true,
        ]);

        return JobListing::factory()->forOrganization($org)->active()->create(array_merge([
            'title' => 'Coordinador Accesible',
            'application_deadline' => now()->addDays(30),
        ], $listingAttrs));
    }

    public function test_listing_has_semantic_landmarks(): void
    {
        $this->makeActiveOffer();

        $response = $this->get('/bolsa-de-trabajo');

        $response->assertOk();
        // Layout landmarks (banner header + main region).
        $response->assertSee('<header', escape: false);
        $response->assertSee('role="banner"', escape: false);
        $response->assertSee('<main id="main"', escape: false);
        $response->assertSee('role="main"', escape: false);
        // Live region announces filter/result updates to AT (FR-013).
        $response->assertSee('aria-live="polite"', escape: false);
        // Filter form has its own labelled landmark.
        $response->assertSee('aria-label="'.__('public.filters.title').'"', escape: false);
    }

    public function test_offer_card_has_proper_heading_hierarchy(): void
    {
        $this->makeActiveOffer(['title' => 'Coordinador Único']);

        $response = $this->get('/bolsa-de-trabajo');

        $response->assertOk();
        $body = (string) $response->getContent();

        // Exactly one <h1> on the listing page (the "Bolsa de Trabajo" title).
        $h1Count = preg_match_all('#<h1\b#i', $body);
        $this->assertSame(1, $h1Count, 'Listing must have exactly one <h1>.');

        // Each offer card titles itself with an <h2> wrapping the offer link.
        $this->assertMatchesRegularExpression(
            '#<h2[^>]*>\s*<a[^>]*>\s*Coordinador Único\s*</a>#s',
            $body,
            'Offer titles must render inside <h2> within each card.'
        );
    }

    public function test_detail_page_has_single_h1_with_offer_title(): void
    {
        $offer = $this->makeActiveOffer(['title' => 'Detalle Único H1']);

        $response = $this->get('/bolsa-de-trabajo/'.$offer->slug);

        $response->assertOk();
        $body = (string) $response->getContent();

        $h1Count = preg_match_all('#<h1\b#i', $body);
        $this->assertSame(1, $h1Count, 'Detail page must have exactly one <h1>.');
        $this->assertStringContainsString('Detalle Único H1', $body);
    }

    public function test_apply_cta_has_aria_label_distinguishing_variant(): void
    {
        $offer = $this->makeActiveOffer(['title' => 'Aria Variant Offer']);

        $response = $this->get('/bolsa-de-trabajo/'.$offer->slug);

        $response->assertOk();
        // Anonymous variant on the page (no auth in this test) → sign-in
        // CTA must include offer title in its aria-label so AT users hear
        // *which* offer they are applying to.
        $response->assertSee('aria-label="'.__('public.cta.anonymous.sign_in').' — Aria Variant Offer"', escape: false);
    }

    public function test_pagination_nav_is_labelled(): void
    {
        // Seed enough offers to get a pagination component on the page.
        $member = Member::factory()->create();
        $org = Organization::factory()->create([
            'member_id' => $member->id,
            'is_active' => true,
        ]);
        JobListing::factory()
            ->count(25)
            ->forOrganization($org)
            ->active()
            ->create(['application_deadline' => now()->addDays(30)]);

        $response = $this->get('/bolsa-de-trabajo');

        $response->assertOk();
        $response->assertSee('rel="next"', escape: false);
        // Layout has a labelled <nav> for top-level navigation; pagination
        // adds its own labelled <nav> for the results paginator.
        $body = (string) $response->getContent();
        $navCount = preg_match_all('#<nav\b[^>]*aria-label=#i', $body);
        $this->assertGreaterThanOrEqual(2, $navCount, 'Listing must contain at least the top nav and the pagination nav, both labelled.');
    }
}
