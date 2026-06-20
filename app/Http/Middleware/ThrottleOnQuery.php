<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Symfony\Component\HttpFoundation\Response;

/**
 * Conditional rate limiter for the public search endpoint (FR-029).
 *
 * Spec 007 caps the keyword search at 60 requests per minute per visitor
 * IP — but only when `q` is present. Listing pagination, sort changes,
 * and pure filter clicks are explicitly NOT throttled so search-engine
 * crawlers can walk paginated URLs freely (FR-022).
 *
 * When triggered, this middleware defers to the standard
 * Illuminate\Routing\Middleware\ThrottleRequests with the named limiter
 * `public-search` (defined in RouteServiceProvider). On excess requests
 * the named limiter renders public.errors.too-many-requests with
 * status 429 and the Retry-After header attached.
 */
class ThrottleOnQuery
{
    public function __construct(private readonly ThrottleRequests $throttle) {}

    public function handle(Request $request, Closure $next, string $limiter = 'public-search'): Response
    {
        if (! $request->filled('q')) {
            return $next($request);
        }

        return $this->throttle->handle($request, $next, $limiter);
    }
}
