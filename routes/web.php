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
