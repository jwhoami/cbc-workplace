<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Enums\JobListingState;
use App\Models\JobListing;
use App\Models\Member;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpiredOfferTest extends TestCase
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

    public function test_previously_active_slug_returns_410(): void
    {
        [$member, $org] = $this->makeOrgAndMember();

        $offer = JobListing::factory()->forOrganization($org)->expired()->create([
            'title' => 'Oferta Expirada',
            'slug' => 'oferta-expirada',
        ]);

        $response = $this->get('/bolsa-de-trabajo/'.$offer->slug);

        $response->assertStatus(410);
        $response->assertSee(__('public.gone.title'));
        $response->assertSee(__('public.gone.message'));
    }

    public function test_unknown_slug_returns_404(): void
    {
        $response = $this->get('/bolsa-de-trabajo/jamas-existio');

        $response->assertStatus(404);
        $response->assertSee(__('public.not_found.title'));
        $response->assertSee(__('public.not_found.message'));
    }

    public function test_org_hidden_returns_410(): void
    {
        [$member, $org] = $this->makeOrgAndMember(orgActive: false);

        $offer = JobListing::factory()->forOrganization($org)->active()->create([
            'title' => 'Oferta de Org Oculta',
            'application_deadline' => now()->addDays(30),
        ]);

        $response = $this->get('/bolsa-de-trabajo/'.$offer->slug);

        $response->assertStatus(410);
    }

    public function test_unpublished_state_returns_410(): void
    {
        [$member, $org] = $this->makeOrgAndMember();

        $offer = JobListing::factory()->forOrganization($org)->pending()->create([
            'title' => 'Oferta Pendiente',
            'application_deadline' => now()->addDays(30),
        ]);

        $response = $this->get('/bolsa-de-trabajo/'.$offer->slug);

        $response->assertStatus(410);
    }

    public function test_active_with_past_deadline_returns_410(): void
    {
        [$member, $org] = $this->makeOrgAndMember();

        // ACTIVE state but deadline already passed — should treat as gone.
        $offer = JobListing::factory()->forOrganization($org)->create([
            'title' => 'Oferta con Deadline Vencido',
            'state' => JobListingState::ACTIVE,
            'application_deadline' => now()->subDays(7),
            'published_at' => now()->subDays(40),
            'approval_by' => 'Admin',
            'approval_at' => now()->subDays(40),
        ]);

        $response = $this->get('/bolsa-de-trabajo/'.$offer->slug);

        $response->assertStatus(410);
    }

    public function test_410_response_has_long_cache_header(): void
    {
        [$member, $org] = $this->makeOrgAndMember();

        $offer = JobListing::factory()->forOrganization($org)->expired()->create();

        $response = $this->get('/bolsa-de-trabajo/'.$offer->slug);

        $response->assertStatus(410);
        $cacheControl = $response->headers->get('Cache-Control', '');
        $this->assertStringContainsString('public', $cacheControl);
        // Gone is gone — long cache is fine and reduces origin load.
        $this->assertStringContainsString('max-age=86400', $cacheControl);
    }
}
