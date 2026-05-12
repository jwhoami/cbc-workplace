<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('app:generate-sitemap')
            ->hourly()
            ->withoutOverlapping()
            ->onOneServer();

        $schedule->command('alerts:dispatch-daily')
            ->dailyAt('07:00')
            ->timezone(config('app.timezone'))
            ->withoutOverlapping()
            ->onOneServer()
            ->runInBackground();

        $schedule->command('alerts:dispatch-weekly')
            ->mondays()
            ->at('07:00')
            ->timezone(config('app.timezone'))
            ->withoutOverlapping()
            ->onOneServer()
            ->runInBackground();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
