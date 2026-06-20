<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Actions\Public\GenerateSitemapAction;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serves the public sitemap (FR-023).
 *
 * Strategy:
 *  - The scheduled `app:generate-sitemap` command writes the canonical
 *    `public/sitemap.xml` once an hour (CLI context, no max_execution_time).
 *  - This controller serves the file when present. When the file is missing
 *    (fresh deploy, scheduler not yet run), it queues a regeneration job
 *    and responds 503 Retry-After so the request never blocks on a full-
 *    table scan and never hits php max_execution_time.
 *  - A short-lived cache lock prevents N concurrent requests from each
 *    queueing N copies of the same job.
 *
 * Lives in the cookie-free `routes/public.php` group so the response is
 * Cloudflare-cacheable.
 */
class SitemapController extends Controller
{
    private const DISPATCH_LOCK_KEY = 'sitemap:dispatch';

    private const DISPATCH_LOCK_TTL = 600;

    private const RETRY_AFTER_SECONDS = 60;

    public function show(): Response
    {
        $path = public_path('sitemap.xml');

        if (! is_file($path)) {
            // Acquire-and-hold (no closure form — that auto-releases). Lock is
            // intentionally not released; TTL expiry is the deduplication window
            // so concurrent requests don't all queue duplicate regenerations
            // before the file appears. If the worker dies mid-job, the lock
            // self-expires after DISPATCH_LOCK_TTL and the next request can
            // re-dispatch.
            $lock = Cache::lock(self::DISPATCH_LOCK_KEY, self::DISPATCH_LOCK_TTL);
            if ($lock->get()) {
                GenerateSitemapAction::dispatch();
            }

            return response('Sitemap is being generated. Please retry shortly.', Response::HTTP_SERVICE_UNAVAILABLE, [
                'Content-Type' => 'text/plain; charset=UTF-8',
                'Retry-After' => (string) self::RETRY_AFTER_SECONDS,
            ]);
        }

        return response((string) file_get_contents($path), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
