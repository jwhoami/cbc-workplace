<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Enums\ContractType;
use App\Enums\WorkModality;
use App\Models\Category;
use App\Models\JobListing;
use App\Models\Member;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilterTest extends TestCase
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

    public function test_single_work_mode_filter_narrows_results(): void
    {
        $org = $this->makeOrg();

        $remote = JobListing::factory()->forOrganization($org)->active()->create([
            'title' => 'Oferta Remota',
            'work_modality' => WorkModality::REMOTE,
            'application_deadline' => now()->addDays(20),
        ]);
        $onSite = JobListing::factory()->forOrganization($org)->active()->create([
            'title' => 'Oferta Presencial',
            'work_modality' => WorkModality::ON_SITE,
            'application_deadline' => now()->addDays(20),
        ]);

        $response = $this->get('/bolsa-de-trabajo?work_mode[]='.WorkModality::REMOTE->value);

        $response->assertOk();
        $response->assertSee('Oferta Remota');
        $response->assertDontSee('Oferta Presencial');
    }

    public function test_multi_select_or_within_same_filter_type(): void
    {
        $org = $this->makeOrg();

        $remote = JobListing::factory()->forOrganization($org)->active()->create([
            'title' => 'Oferta Remota Multi',
            'work_modality' => WorkModality::REMOTE,
            'application_deadline' => now()->addDays(20),
        ]);
        $hybrid = JobListing::factory()->forOrganization($org)->active()->create([
            'title' => 'Oferta Híbrida Multi',
            'work_modality' => WorkModality::HYBRID,
            'application_deadline' => now()->addDays(20),
        ]);
        JobListing::factory()->forOrganization($org)->active()->create([
            'title' => 'Oferta Presencial Multi',
            'work_modality' => WorkModality::ON_SITE,
            'application_deadline' => now()->addDays(20),
        ]);

        $response = $this->get(
            '/bolsa-de-trabajo?work_mode[]='.WorkModality::REMOTE->value
            .'&work_mode[]='.WorkModality::HYBRID->value
        );

        $response->assertOk();
        $response->assertSee('Oferta Remota Multi');
        $response->assertSee('Oferta Híbrida Multi');
        $response->assertDontSee('Oferta Presencial Multi');
    }

    public function test_and_across_different_filter_types(): void
    {
        $org = $this->makeOrg();

        $match = JobListing::factory()->forOrganization($org)->active()->create([
            'title' => 'Match Exacto',
            'work_modality' => WorkModality::REMOTE,
            'contract_type' => ContractType::FULL_TIME,
            'application_deadline' => now()->addDays(20),
        ]);
        JobListing::factory()->forOrganization($org)->active()->create([
            'title' => 'Solo Remoto Voluntario',
            'work_modality' => WorkModality::REMOTE,
            'contract_type' => ContractType::VOLUNTEER,
            'application_deadline' => now()->addDays(20),
        ]);
        JobListing::factory()->forOrganization($org)->active()->create([
            'title' => 'Solo Tiempo Completo Presencial',
            'work_modality' => WorkModality::ON_SITE,
            'contract_type' => ContractType::FULL_TIME,
            'application_deadline' => now()->addDays(20),
        ]);

        $response = $this->get(
            '/bolsa-de-trabajo?work_mode[]='.WorkModality::REMOTE->value
            .'&contract[]='.ContractType::FULL_TIME->value
        );

        $response->assertOk();
        $response->assertSee('Match Exacto');
        $response->assertDontSee('Solo Remoto Voluntario');
        $response->assertDontSee('Solo Tiempo Completo Presencial');
    }

    public function test_keyword_plus_filters_intersection(): void
    {
        $org = $this->makeOrg();

        JobListing::factory()->forOrganization($org)->active()->create([
            'title' => 'Diseñador remoto',
            'work_modality' => WorkModality::REMOTE,
            'application_deadline' => now()->addDays(20),
        ]);
        JobListing::factory()->forOrganization($org)->active()->create([
            'title' => 'Diseñador presencial',
            'work_modality' => WorkModality::ON_SITE,
            'application_deadline' => now()->addDays(20),
        ]);
        JobListing::factory()->forOrganization($org)->active()->create([
            'title' => 'Contador remoto',
            'work_modality' => WorkModality::REMOTE,
            'application_deadline' => now()->addDays(20),
        ]);

        $response = $this->get('/bolsa-de-trabajo?q=disenador&work_mode[]='.WorkModality::REMOTE->value);

        $response->assertOk();
        $response->assertSee('Diseñador remoto');
        $response->assertDontSee('Diseñador presencial');
        $response->assertDontSee('Contador remoto');
    }

    public function test_clear_all_link_returns_to_unfiltered_listing(): void
    {
        $org = $this->makeOrg();

        JobListing::factory()->count(2)->forOrganization($org)->active()->create([
            'application_deadline' => now()->addDays(20),
        ]);

        $response = $this->get('/bolsa-de-trabajo?q=nada-que-coincida');
        $response->assertOk();
        $response->assertSee(__('public.filters.clear_all'));
        $response->assertSee('href="'.url('/bolsa-de-trabajo').'"', escape: false);
    }

    public function test_category_filter_via_morph_relation(): void
    {
        $org = $this->makeOrg();

        $cat = Category::create([
            'name' => 'Tecnología',
            'scope' => 'JobListing',
            'order' => 0,
        ]);

        $matching = JobListing::factory()->forOrganization($org)->active()->create([
            'title' => 'Puesto en Tecnología',
            'application_deadline' => now()->addDays(20),
        ]);
        $matching->categories()->attach($cat->id);

        JobListing::factory()->forOrganization($org)->active()->create([
            'title' => 'Puesto sin categoría',
            'application_deadline' => now()->addDays(20),
        ]);

        $response = $this->get('/bolsa-de-trabajo?category[]='.$cat->id);

        $response->assertOk();
        $response->assertSee('Puesto en Tecnología');
        $response->assertDontSee('Puesto sin categoría');
    }

    public function test_filter_with_no_matches_renders_filtered_empty_state(): void
    {
        $org = $this->makeOrg();

        JobListing::factory()->forOrganization($org)->active()->create([
            'work_modality' => WorkModality::ON_SITE,
            'application_deadline' => now()->addDays(20),
        ]);

        $response = $this->get('/bolsa-de-trabajo?work_mode[]='.WorkModality::REMOTE->value);

        $response->assertOk();
        $response->assertSee(__('public.listing.empty.with_filters.title'));
        $response->assertSee(__('public.listing.empty.with_filters.cta'));
    }
}
