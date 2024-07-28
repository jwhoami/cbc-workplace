<?php

namespace App\Actions\Member;

use App\Helpers\Util;
use App\Models\Venture;
use Illuminate\Support\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

class ExtendValidity
{
  use AsAction;

  public function handle(Venture $venture)
  {
    if (! $venture->is_expired) {
      Util::filamentNotification('Este empredimiento aún no ha vencido', "warning");
      return;
    }
    if (! $venture->is_extendable) {
      Util::filamentNotification('Este empredimiento no es extendible', "warning");
      return;
    }
    $venture->expires_at = now()->addDays(90);
    $venture->is_expired = 0;
    $venture->is_active = 1;
    $venture->save();

    $venture->addComment(__("Emprendimiento extendido hasta :0", [$venture->expires_at->format('Y-m-d')]));
  }
}
