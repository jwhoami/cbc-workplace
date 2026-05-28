<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Models\JobListing;
use App\Models\Member;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrowseJobBoardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function makeOrgAndMember(bool $orgActive = true): array
    {
        $member = Member::factory()->create();
        $org = Organization::factory()->create([
            'member_id' => $member->id,
            'is_active' => $orgActive,
        ]);

        return [$member, $org];
    }

    public function test_listing_renders_200_for_anonymous_visitor(): void
    {
        $response = $this->get('/bolsa-de-trabajo');

        $response->assertOk();
        $response->assertSee(__('public.listing.title'));
    }

    public function test_listing_shows_only_active_offers_from_visible_orgs(): void
    {
        [$member, $org] = $this->makeOrgAndMember();
        [$hiddenMember, $hiddenOrg] = $this->makeOrgAndMember(orgActive: false);

        $visible = JobListing::factory()->forOrganization($org)->active()->create([
            'title' => 'Diseñador Visible',
            'application_deadline' => now()->addDays(30),
        ]);

        JobListing::factory()->forOrganization($org)->expired()->create([
            'title' => 'Oferta Expirada',
        ]);

        JobListing::factory()->forOrganization($org)->pending()->create([
            'title' => 'Oferta Pendiente',
        ]);

        JobListing::factory()->forOrganization($hiddenOrg)->active()->create([
            'title' => 'Oferta Org Oculta',
            'application_deadline' => now()->addDays(30),
        ]);

        $response = $this->get('/bolsa-de-trabajo');

        $response->assertOk();
        $response->assertSee('Diseñador Visible');
        $response->assertDontSee('Oferta Expirada');
        $response->assertDontSee('Oferta Pendiente');
        $response->assertDontSee('Oferta Org Oculta');
    }

    public function test_empty_state_when_no_active_offers(): void
    {
        [$member, $org] = $this->makeOrgAndMember();

        JobListing::factory()->forOrganization($org)->expired()->create();

        $response = $this->get('/bolsa-de-trabajo');

        $response->assertOk();
        $response->assertSee(__('public.listing.empty.title'));
        $response->assertSee(__('public.listing.empty.message'));
    }

    public function test_default_sort_is_most_recent_first(): void
    {
        [$member, $org] = $this->makeOrgAndMember();

        JobListing::factory()->forOrganization($org)->active()->create([
            'title' => 'Oferta Antigua',
            'application_deadline' => now()->addDays(30),
            'published_at' => now()->subDays(20),
        ]);

        JobListing::factory()->forOrganization($org)->active()->create([
            'title' => 'Oferta Reciente',
            'application_deadline' => now()->addDays(30),
            'published_at' => now()->subDays(1),
        ]);

        $response = $this->get('/bolsa-de-trabajo');

        $response->assertOk();
        $body = $response->getContent();
        $posReciente = strpos($body, 'Oferta Reciente');
        $posAntigua = strpos($body, 'Oferta Antigua');

        $this->assertNotFalse($posReciente);
        $this->assertNotFalse($posAntigua);
        $this->assertLessThan($posAntigua, $posReciente, 'Most-recent offer must appear before older offer (FR-006).');
    }

    public function test_row_content_matches_fr008(): void
    {
        [$member, $org] = $this->makeOrgAndMember();

        $org->update(['display_name' => 'Fundación de Prueba']);

        JobListing::factory()->forOrganization($org)->active()->create([
            'title' => 'Coordinador de Eventos',
            'city' => 'San José',
            'application_deadline' => now()->addDays(20),
            'published_at' => now()->subDays(2),
        ]);

        $response = $this->get('/bolsa-de-trabajo');

        $response->assertOk();
        $response->assertSee('Coordinador de Eventos');
        $response->assertSee('Fundación de Prueba');
        $response->assertSee('San José');
        $response->assertSee(__('public.listing.row.work_mode'));
        $response->assertSee(__('public.listing.row.contract_type'));
    }

    public function test_listing_response_has_no_session_cookie(): void
    {
        $response = $this->get('/bolsa-de-trabajo');

        $response->assertOk();
        $cookies = $response->headers->getCookies();

        foreach ($cookies as $cookie) {
            $this->assertStringNotContainsString(
                'session',
                strtolower($cookie->getName()),
                "Public listing must not emit session cookie '{$cookie->getName()}' — Cloudflare can't cache responses with cookies."
            );
            $this->assertStringNotContainsString(
                'xsrf',
                strtolower($cookie->getName()),
                "Public listing must not emit XSRF cookie '{$cookie->getName()}'."
            );
        }
    }

    public function test_listing_emits_public_cache_control(): void
    {
        $response = $this->get('/bolsa-de-trabajo');

        $response->assertOk();
        $cacheControl = $response->headers->get('Cache-Control', '');

        $this->assertStringContainsString('public', $cacheControl);
        $this->assertStringNotContainsString('private', $cacheControl);
    }

    public function test_listing_includes_canonical_link(): void
    {
        $response = $this->get('/bolsa-de-trabajo');

        $response->assertOk();
        $response->assertSee('rel="canonical"', escape: false);
        $response->assertSee('href="'.url('/bolsa-de-trabajo').'"', escape: false);
    }

    public function test_logged_in_member_is_redirected_to_dashboard(): void
    {
        $member = \App\Models\Member::factory()->create();
        $cookieName = config('session.cookie');

        // We act as the member and supply the session cookie to trigger the middleware check
        $response = $this->actingAs($member, 'member')
            ->withCookie($cookieName, 'dummy-session-id')
            ->get('/bolsa-de-trabajo');

        $response->assertRedirect('/member');
    }
}
