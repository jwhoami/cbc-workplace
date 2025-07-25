<?php

namespace App\Console\Commands;

use App\Mail\Member\VentureExpired;
use App\Models\Venture;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
    Log::info("Running delete expired ventures command");
    $expiration = now()->subDays(30);

    Venture::query()
      ->where('is_expired', 1)
      ->where('expires_at', '<', $expiration)
      ->get()
      ->each(function ($venture) {
        $venture->delete();
      });
  }
}
