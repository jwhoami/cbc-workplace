<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Strips session cookies and ensures responses on public read routes are CDN-cacheable.
 *
 * Spec 007 places the public listing/detail/sitemap routes outside the `web` middleware
 * group precisely so that the session is never started — but Laravel still attaches an
 * XSRF cookie via global middleware in some flows. This middleware:
 *
 *   1. Strips any `Set-Cookie` headers from the outbound response (defensive — there
 *      should be none at this layer, but we belt-and-braces it for FR-013 + Cloudflare
 *      cache eligibility).
 *   2. Replaces a default `Cache-Control: private` (Laravel's default for HTML responses)
 *      with a public, short-TTL value tuned for SC-001's 2-second budget.
 *
 * Only applied on idempotent GET/HEAD requests; other verbs are passed through untouched.
 */
class PublicNoSessionCookie
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if (! in_array($request->getMethod(), ['GET', 'HEAD'], true)) {
            return $response;
        }

        // Symfony stores cookies in a separate bag from the raw Set-Cookie
        // header; we have to iterate the bag and clear each one so the
        // response goes out cookie-free (CDN cache eligibility).
        foreach ($response->headers->getCookies() as $cookie) {
            $response->headers->removeCookie($cookie->getName(), $cookie->getPath(), $cookie->getDomain());
        }
        $response->headers->remove('Set-Cookie');

        $existingCacheControl = $response->headers->get('Cache-Control', '');
        if ($existingCacheControl === '' || str_contains($existingCacheControl, 'private')) {
            $response->headers->set(
                'Cache-Control',
                'public, max-age=60, stale-while-revalidate=600'
            );
        }

        return $response;
    }
}
