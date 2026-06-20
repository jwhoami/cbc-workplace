<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

// Artisan::command('inspire', function () {
//    $this->comment(Inspiring::quote());
// })->purpose('Display an inspiring quote');

Schedule::command(\App\Console\Commands\ExpireVentures::class)->daily();
Schedule::command(\App\Console\Commands\DeleteExpiredVentures::class)->daily();
Schedule::call(fn () => \App\Actions\ExpireJobListings::run())->daily();
