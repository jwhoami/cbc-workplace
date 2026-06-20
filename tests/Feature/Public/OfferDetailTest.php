<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Models\JobListing;
use App\Models\Member;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfferDetailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function makeActiveOffer(array $listingAttrs = [], array $orgAttrs = []): JobListing
    {
        $member = Member::factory()->create();
        $org = Organization::factory()->create(array_merge([
            'member_id' => $member->id,
            'is_active' => true,
            'display_name' => 'Fundación de Prueba',
        ], $orgAttrs));

        return JobListing::factory()->forOrganization($org)->active()->create(array_merge([
            'title' => 'Coordinador de Eventos',
            'description' => 'Buscamos un coordinador con experiencia en eventos masivos.',
            'requirements' => 'Mínimo 2 años de experiencia.',
            'city' => 'San José',
            'province' => 'San José',
            'application_deadline' => now()->addDays(30),
        ], $listingAttrs));
    }

    public function test_active_offer_renders_all_fr014_fields(): void
    {
        $offer = $this->makeActiveOffer([
            'salary_min' => 1500,
            'salary_max' => 2500,
        ]);

        $response = $this->get('/bolsa-de-trabajo/'.$offer->slug);

        $response->assertOk();
        $response->assertSee($offer->title);
        $response->assertSee($offer->description);
        $response->assertSee($offer->requirements);
        $response->assertSee($offer->city);
        $response->assertSee('Fundación de Prueba');
        $response->assertSee(__('public.detail.work_mode'));
        $response->assertSee(__('public.detail.contract_type'));
        $response->assertSee(__('public.detail.publication_date'));
        $response->assertSee(__('public.detail.application_deadline'));
        $response->assertSee('1,500.00');
        $response->assertSee('2,500.00');
    }

    public function test_optional_salary_is_omitted_when_null(): void
    {
        $offer = $this->makeActiveOffer([
            'salary_min' => null,
            'salary_max' => null,
        ]);

        $response = $this->get('/bolsa-de-trabajo/'.$offer->slug);

        $response->assertOk();
        $response->assertSee(__('public.detail.salary_unspecified'));
    }

    public function test_canonical_link_is_present_and_correct(): void
    {
        $offer = $this->makeActiveOffer();

        $response = $this->get('/bolsa-de-trabajo/'.$offer->slug);

        $response->assertOk();
        $response->assertSee('rel="canonical"', escape: false);
        $response->assertSee('href="'.url('/bolsa-de-trabajo/'.$offer->slug).'"', escape: false);
    }

    public function test_jsonld_validates_as_jobposting(): void
    {
        $offer = $this->makeActiveOffer([
            'salary_min' => 1000,
            'salary_max' => 1500,
        ]);

        $response = $this->get('/bolsa-de-trabajo/'.$offer->slug);

        $response->assertOk();
        $body = $response->getContent();

        // Extract JSON-LD payload
        preg_match('#<script type="application/ld\+json">\s*(.+?)\s*</script>#s', $body, $matches);
        $this->assertNotEmpty($matches, 'JSON-LD script tag must be present (FR-025).');

        $jsonld = json_decode($matches[1], true);
        $this->assertIsArray($jsonld);
        $this->assertSame('https://schema.org/', $jsonld['@context']);
        $this->assertSame('JobPosting', $jsonld['@type']);
        $this->assertSame($offer->title, $jsonld['title']);
        $this->assertArrayHasKey('hiringOrganization', $jsonld);
        $this->assertSame('Fundación de Prueba', $jsonld['hiringOrganization']['name']);
        $this->assertArrayHasKey('jobLocation', $jsonld);
        $this->assertSame('San José', $jsonld['jobLocation']['address']['addressLocality']);
        $this->assertArrayHasKey('datePosted', $jsonld);
        $this->assertArrayHasKey('validThrough', $jsonld);
        $this->assertArrayHasKey('baseSalary', $jsonld);
    }

    public function test_og_tags_present_for_share_preview(): void
    {
        $offer = $this->makeActiveOffer();

        $response = $this->get('/bolsa-de-trabajo/'.$offer->slug);

        $response->assertOk();
        $response->assertSee('property="og:title"', escape: false);
        $response->assertSee('property="og:description"', escape: false);
        $response->assertSee('property="og:url"', escape: false);
        $response->assertSee('property="og:type"', escape: false);
        $response->assertSee('name="twitter:card"', escape: false);
    }

    public function test_anonymous_visitor_sees_signin_cta(): void
    {
        $offer = $this->makeActiveOffer();

        $response = $this->get('/bolsa-de-trabajo/'.$offer->slug);

        $response->assertOk();
        $response->assertSee(__('public.cta.anonymous.sign_in'));
        $response->assertDontSee(__('public.cta.member_candidate.button'));
    }

    public function test_detail_response_emits_public_cache_control(): void
    {
        $offer = $this->makeActiveOffer();

        $response = $this->get('/bolsa-de-trabajo/'.$offer->slug);

        $response->assertOk();
        $cacheControl = $response->headers->get('Cache-Control', '');
        $this->assertStringContainsString('public', $cacheControl);
        $this->assertStringContainsString('max-age=300', $cacheControl);
    }
}
