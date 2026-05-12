<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/member/tos', \App\Filament\Member\Pages\Tos::class)
    ->name('member-tos');
Route::get('/member/welcome', \App\Filament\Member\Pages\Welcome::class)
    ->name('member-welcome');
Route::get('/member/contact', \App\Filament\Member\Pages\Contact::class)
    ->name('member-contact');
Route::get('/member/register-with-invitation-code', \App\Filament\Member\Pages\InvitationCodeRequiredForRegistration::class)
    ->name('member-register-with-invitation-code');
Route::get('/app', \App\Filament\Venture\Resources\VentureResource\Pages\ListVentures::class)
    ->name('venture-home');

// Spec 007 public job-board listing lives in routes/public.php (loaded by
// RouteServiceProvider with a minimal, session-free middleware stack) so
// Cloudflare can cache the listing at the edge (FR-013, SC-001).
//
// The offer detail route below intentionally uses the standard `web` group:
// FR-019's variant-aware Apply CTA needs to read Auth state, which requires
// the session middleware. Variant-personalized responses are not edge-
// cacheable in any case, so the cookie cost is acceptable.
Route::get('/bolsa-de-trabajo/{slug}', [\App\Http\Controllers\Public\JobOfferController::class, 'show'])
    ->where('slug', '[a-z0-9-]+')
    ->name('public.job-offer.show');

// Spec 008 — anonymous unsubscribe via signed URL (FR-027..FR-028c).
// Long-lived signed URL: `URL::signedRoute(..., absoluteExpiresAt: null)`.
Route::get('/alerts/unsubscribe/{member}/{alert}', \App\Http\Controllers\Member\UnsubscribeAlertController::class)
    ->middleware(['signed'])
    ->name('alerts.unsubscribe');
