<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Enums\PublicEventKind;
use App\Models\JobListing;
use App\Models\Member;
use App\Models\Organization;
use App\Models\PublicEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicEventTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_listing_visit_emits_pageview_event(): void
    {
        $this->assertSame(0, PublicEvent::count());

        $this->get('/bolsa-de-trabajo')->assertOk();

        $this->assertSame(1, PublicEvent::count());

        /** @var PublicEvent $event */
        $event = PublicEvent::first();

        $this->assertSame(PublicEventKind::PageView, $event->kind);
        $this->assertSame('/bolsa-de-trabajo', $event->path);
        $this->assertSame('anonymous', $event->visitor_variant);
        $this->assertSame(1, $event->page_number);
        $this->assertNotNull($event->correlation_id);
        $this->assertNotNull($event->occurred_at);
    }

    public function test_pageview_event_records_paginated_page_number(): void
    {
        $member = Member::factory()->create();
        $org = Organization::factory()->create([
            'member_id' => $member->id,
            'is_active' => true,
        ]);

        // Seed >20 active offers so page=2 has content.
        JobListing::factory()
            ->count(25)
            ->forOrganization($org)
            ->active()
            ->create([
                'application_deadline' => now()->addDays(30),
            ]);

        $this->get('/bolsa-de-trabajo?page=2')->assertOk();

        /** @var PublicEvent $event */
        $event = PublicEvent::latest()->first();

        $this->assertSame(2, $event->page_number);
        $this->assertSame('page=2', $event->query_string);
    }

    public function test_pageview_event_payload_is_empty_array(): void
    {
        $this->get('/bolsa-de-trabajo')->assertOk();

        $event = PublicEvent::first();

        $this->assertNotNull($event);
        // PageView events carry no extra payload — path/query/page cover it (data-model.md §2).
        $this->assertContains($event->payload, [null, []]);
    }

    public function test_detail_open_emits_event_with_slug_payload(): void
    {
        $member = Member::factory()->create();
        $org = Organization::factory()->create([
            'member_id' => $member->id,
            'is_active' => true,
        ]);
        $offer = JobListing::factory()->forOrganization($org)->active()->create([
            'title' => 'Coordinador de Eventos',
            'application_deadline' => now()->addDays(30),
        ]);

        $this->get('/bolsa-de-trabajo/'.$offer->slug)->assertOk();

        $detailEvents = PublicEvent::where('kind', PublicEventKind::DetailOpen)->get();
        $this->assertCount(1, $detailEvents);

        $event = $detailEvents->first();
        $this->assertSame('/bolsa-de-trabajo/'.$offer->slug, $event->path);
        $this->assertSame('anonymous', $event->visitor_variant);
        $this->assertIsArray($event->payload);
        $this->assertSame($offer->slug, $event->payload['slug']);
        $this->assertSame($offer->id, $event->payload['offer_id']);
    }

    public function test_410_does_not_emit_detail_open_event(): void
    {
        $member = Member::factory()->create();
        $org = Organization::factory()->create([
            'member_id' => $member->id,
            'is_active' => true,
        ]);
        $offer = JobListing::factory()->forOrganization($org)->expired()->create();

        $this->get('/bolsa-de-trabajo/'.$offer->slug)->assertStatus(410);

        // 410/404 paths are NOT analytics-relevant for spec 009 — only successful
        // detail opens count toward the DetailOpen event stream.
        $this->assertSame(
            0,
            PublicEvent::where('kind', PublicEventKind::DetailOpen)->count()
        );
    }

    public function test_keyword_query_emits_event_with_folded_keyword(): void
    {
        $this->get('/bolsa-de-trabajo?q=Diseñador')->assertOk();

        $events = PublicEvent::where('kind', PublicEventKind::KeywordQuery)->get();
        $this->assertCount(1, $events);

        $event = $events->first();
        $this->assertIsArray($event->payload);
        $this->assertSame('disenador', $event->payload['folded_keyword']);
        $this->assertSame(9, $event->payload['raw_length']);
        $this->assertArrayHasKey('active_filters', $event->payload);
    }

    public function test_filter_change_event_emitted_when_filter_active(): void
    {
        $this->get('/bolsa-de-trabajo?work_mode[]=1')->assertOk();

        $events = PublicEvent::where('kind', PublicEventKind::FilterChange)->get();
        $this->assertCount(1, $events);

        $event = $events->first();
        $this->assertIsArray($event->payload);
        $this->assertSame('apply', $event->payload['action']);
        $this->assertContains(1, $event->payload['filters']['work_mode']);
    }

    public function test_no_filter_event_when_only_browsing(): void
    {
        $this->get('/bolsa-de-trabajo')->assertOk();

        $this->assertSame(0, PublicEvent::where('kind', PublicEventKind::FilterChange)->count());
        $this->assertSame(0, PublicEvent::where('kind', PublicEventKind::KeywordQuery)->count());
    }
}
