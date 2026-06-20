<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Actions\Public\SearchPublicOffersAction;
use App\Enums\PublicEventKind;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use RuntimeException;
use Tests\TestCase;

/**
 * Phase 8 — error-state UX (FR-030, FR-031). Two paths converge on the
 * same friendly Blade view:
 *  - Uncaught Throwable from a search action → 5xx + ErrorShown event.
 *  - Bad query input (e.g. unsupported `sort=` value) → 400 + same view.
 */
class ErrorStateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        // Re-raise rendering exceptions in the test would defeat the
        // purpose of testing the renderable() callback, so do NOT call
        // withoutExceptionHandling().
    }

    public function test_500_on_listing_renders_friendly_error(): void
    {
        // Mock the search action to blow up.
        $action = Mockery::mock(SearchPublicOffersAction::class);
        $action->shouldReceive('handle')
            ->andThrow(new RuntimeException('storage unreachable'));
        $this->app->instance(SearchPublicOffersAction::class, $action);

        $response = $this->get('/bolsa-de-trabajo');

        $response->assertStatus(500);
        $response->assertSee(__('public.error.title'));
        $response->assertSee(__('public.error.message'));

        // FR-031: the failure must surface as an ErrorShown public event.
        $this->assertDatabaseHas('public_events', [
            'kind' => PublicEventKind::ErrorShown->value,
        ]);
    }

    public function test_invalid_query_input_renders_error_state(): void
    {
        $response = $this->get('/bolsa-de-trabajo?sort=invalid');

        $response->assertStatus(400);
        $response->assertSee(__('public.error.title'));
        $response->assertSee(__('public.error.message'));

        // Crucially NOT a 302 redirect-back-with-errors and NOT a JSON dump.
        $this->assertStringStartsWith(
            'text/html',
            (string) $response->headers->get('Content-Type'),
            'Public surface must render HTML, not application/json.'
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
