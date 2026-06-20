<?php

namespace App\Console\Commands;

use App\Models\Config;
use App\Models\Venture;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DeleteExpiredVentures extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-expired-ventures';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Eliminar emprendimientos vencidos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('Running delete expired ventures command');
        $expiration = now()->subDays(Config::make()->getp('ventures.deleteExpiredVenturesAfterDays', 30));

        Venture::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', $expiration)
            ->where('is_expired', 1)
            ->get()
            ->each(function ($venture) {
                $venture->delete();
            });
    }
}
