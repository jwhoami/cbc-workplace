<?php

declare(strict_types=1);

use App\Http\Controllers\Public\JobBoardController;
use App\Http\Controllers\Public\SitemapController;
use App\Http\Middleware\ThrottleOnQuery;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Job Board Routes (spec 007)
|--------------------------------------------------------------------------
|
| Loaded by RouteServiceProvider with a minimal middleware stack — NO
| StartSession, NO VerifyCsrfToken, NO EncryptCookies — so responses are
| cookie-free and Cloudflare-cacheable. The PublicNoSessionCookie
| middleware additionally strips any cookies that crept in from anywhere
| else in the pipeline.
|
| All routes are anonymous (FR-001, FR-002), GET-only, idempotent.
|
*/

Route::name('public.')->group(function () {
    Route::get('/bolsa-de-trabajo', JobBoardController::class)
        ->middleware([
            ThrottleOnQuery::class,
            \App\Http\Middleware\RedirectIfMemberLoggedIn::class,
        ])
        ->name('job-board.index');

    // Detail route /bolsa-de-trabajo/{slug} lives in routes/web.php — it
    // needs the standard `web` middleware group for variant CTA detection
    // (FR-019) which requires session middleware.

    Route::get('/sitemap.xml', [SitemapController::class, 'show'])
        ->name('sitemap');
});
