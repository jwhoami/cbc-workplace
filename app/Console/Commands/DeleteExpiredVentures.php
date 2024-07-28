<?php

namespace App\Console\Commands;

use App\Mail\Member\VentureExpired;
use App\Models\Venture;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
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
    $expiration = now()->subDays(5);
    DB::table('ventures')
      ->where('is_expired', 1)
      ->where('expires_at', '<', $expiration)
      ->delete();
  }
}
