<?php

namespace App\Console\Commands;

use App\Actions\Member\MarkVentureAsExpired;
use App\Models\Venture;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpireVentures extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:expire-ventures';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Marcar emprendimientos como vencido';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('Running expire ventures command');
        $expiration = now();
        Venture::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', $expiration)
            ->where('is_expired', 0)
            ->get()
            ->each(function ($venture) {
                MarkVentureAsExpired::run($venture);
            });
    }
}
