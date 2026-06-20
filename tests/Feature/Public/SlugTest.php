<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Models\JobListing;
use App\Models\Member;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlugTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_slug_is_generated_from_title_when_empty(): void
    {
        $org = $this->makeOrg();

        $offer = JobListing::factory()->forOrganization($org)->draft()->create([
            'title' => 'Diseñador Gráfico Júnior',
        ]);

        $this->assertNotEmpty($offer->slug);
        $this->assertSame('disenador-grafico-junior', $offer->slug);
    }

    public function test_slug_only_contains_lowercase_ascii_and_hyphens(): void
    {
        $org = $this->makeOrg();

        $offer = JobListing::factory()->forOrganization($org)->draft()->create([
            'title' => 'Coordinador de Eventos & Proyectos Especiales!',
        ]);

        $this->assertMatchesRegularExpression(
            '/^[a-z0-9-]+$/',
            $offer->slug,
            "Slug must be lowercase ASCII with hyphens only (FR-016). Got: {$offer->slug}"
        );
    }

    public function test_slug_collision_appends_numeric_disambiguator(): void
    {
        $org = $this->makeOrg();

        $first = JobListing::factory()->forOrganization($org)->draft()->create([
            'title' => 'Pastor de Jóvenes',
        ]);
        $second = JobListing::factory()->forOrganization($org)->draft()->create([
            'title' => 'Pastor de Jóvenes',
        ]);
        $third = JobListing::factory()->forOrganization($org)->draft()->create([
            'title' => 'Pastor de Jóvenes',
        ]);

        $this->assertSame('pastor-de-jovenes', $first->slug);
        $this->assertSame('pastor-de-jovenes-2', $second->slug);
        $this->assertSame('pastor-de-jovenes-3', $third->slug);
    }

    public function test_route_constraint_rejects_invalid_slug_chars(): void
    {
        // Underscores are not valid per FR-016 — route's [a-z0-9-]+ constraint rejects them.
        $response = $this->get('/bolsa-de-trabajo/Some_Invalid_Slug');

        $response->assertStatus(404);
    }
}
