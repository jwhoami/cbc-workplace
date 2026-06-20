<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Models\JobListing;
use App\Models\Member;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class RateLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        // Reset the named limiter bucket for the test IP.
        RateLimiter::clear(sha1('public-search|127.0.0.1'));
    }

    private function makeOrgWithOffer(): void
    {
        $member = Member::factory()->create();
        $org = Organization::factory()->create([
            'member_id' => $member->id,
            'is_active' => true,
        ]);
        JobListing::factory()->forOrganization($org)->active()->create([
            'application_deadline' => now()->addDays(20),
        ]);
    }

    public function test_listing_pagination_is_not_throttled(): void
    {
        $this->makeOrgWithOffer();

        // 70 requests without `q` → all should be 200 (no throttle for crawlers).
        for ($i = 1; $i <= 70; $i++) {
            $response = $this->get('/bolsa-de-trabajo?page=1');
            $this->assertSame(
                200,
                $response->status(),
                "Listing without keyword must not be throttled (request #{$i} returned {$response->status()})."
            );
        }
    }

    public function test_61st_keyword_request_returns_429(): void
    {
        $this->makeOrgWithOffer();

        // 60 keyword queries within 60s should all succeed.
        for ($i = 1; $i <= 60; $i++) {
            $response = $this->get('/bolsa-de-trabajo?q=test'.$i);
            $this->assertSame(
                200,
                $response->status(),
                "Keyword request #{$i} should still be within the 60/min cap, got {$response->status()}."
            );
        }

        // The 61st should hit the limiter.
        $response = $this->get('/bolsa-de-trabajo?q=overflow');
        $response->assertStatus(429);
        $this->assertNotNull($response->headers->get('Retry-After'));
        $response->assertSee(__('public.too_many_requests.title'));
    }

    public function test_429_response_uses_friendly_spanish_view(): void
    {
        $this->makeOrgWithOffer();

        // Burn the limit
        for ($i = 1; $i <= 60; $i++) {
            $this->get('/bolsa-de-trabajo?q=q'.$i);
        }

        $response = $this->get('/bolsa-de-trabajo?q=triggers-429');

        $response->assertStatus(429);
        // No stack trace, no Laravel default 429 page — friendly Spanish copy.
        $response->assertSee(__('public.too_many_requests.title'));
        $response->assertSee(__('public.too_many_requests.message'));
        $response->assertDontSee('Stack trace');
        $response->assertDontSee('vendor/laravel');
    }
}
