<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Models\JobListing;
use App\Models\Member;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KeywordSearchTest extends TestCase
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

    public function test_accent_insensitive_match_in_both_directions(): void
    {
        $org = $this->makeOrg();

        JobListing::factory()->forOrganization($org)->active()->create([
            'title' => 'Diseñador junior con experiencia',
            'description' => 'Tareas variadas',
            'application_deadline' => now()->addDays(20),
        ]);

        // Unaccented query → accented title
        $this->get('/bolsa-de-trabajo?q=disenador')
            ->assertOk()
            ->assertSee('Diseñador junior con experiencia');

        // Accented query → accented title
        $this->get('/bolsa-de-trabajo?q=diseñador')
            ->assertOk()
            ->assertSee('Diseñador junior con experiencia');
    }

    public function test_partial_word_match(): void
    {
        $org = $this->makeOrg();

        JobListing::factory()->forOrganization($org)->active()->create([
            'title' => 'Diseñador',
            'description' => 'Sin descripción adicional',
            'application_deadline' => now()->addDays(20),
        ]);

        $this->get('/bolsa-de-trabajo?q=dise')
            ->assertOk()
            ->assertSee('Diseñador');
    }

    public function test_keyword_searches_both_title_and_description(): void
    {
        $org = $this->makeOrg();

        $matchTitle = JobListing::factory()->forOrganization($org)->active()->create([
            'title' => 'Coordinador con habilidad palabra-clave',
            'description' => 'Tareas variadas',
            'application_deadline' => now()->addDays(20),
        ]);
        $matchDesc = JobListing::factory()->forOrganization($org)->active()->create([
            'title' => 'Asistente Administrativo',
            'description' => 'Buscamos persona con palabra-clave en su perfil',
            'application_deadline' => now()->addDays(20),
        ]);
        JobListing::factory()->forOrganization($org)->active()->create([
            'title' => 'Otro puesto',
            'description' => 'Sin coincidencia',
            'application_deadline' => now()->addDays(20),
        ]);

        $response = $this->get('/bolsa-de-trabajo?q=palabra-clave');

        $response->assertOk();
        $response->assertSee($matchTitle->title);
        $response->assertSee($matchDesc->title);
        $response->assertDontSee('Otro puesto');
    }

    public function test_special_characters_safe_against_injection(): void
    {
        $org = $this->makeOrg();

        JobListing::factory()->forOrganization($org)->active()->create([
            'title' => 'Especialista en Marketing',
            'application_deadline' => now()->addDays(20),
        ]);

        // Throw classic SQL-injection attempt at the keyword endpoint.
        // Should: (a) not 5xx, (b) not return all offers, (c) treat as a
        // literal substring match → returns zero results gracefully.
        $response = $this->get("/bolsa-de-trabajo?q=%25';DROP TABLE offers;--");

        $response->assertOk();
        $response->assertSee(__('public.listing.empty.with_filters.title'));
    }

    public function test_empty_keyword_returns_all_active_offers(): void
    {
        $org = $this->makeOrg();

        JobListing::factory()->count(3)->forOrganization($org)->active()->create([
            'application_deadline' => now()->addDays(20),
        ]);

        $response = $this->get('/bolsa-de-trabajo?q=');

        $response->assertOk();
        $response->assertSee('3 ofertas');
    }
}
