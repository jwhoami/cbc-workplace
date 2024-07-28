<?php

namespace App\Actions\Admin;

use App\Helpers\Util;
use App\Mail\Member\VentureActiveToggled;
use App\Models\Venture;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;

class VentureToggleActive
{
  use AsAction;

  public function handle(Venture $venture, array $data)
  {
    if (! $venture->is_active && $venture->is_expired && ! $venture->is_extendable) {
      Util::filamentNotification('No se puede activar el emprendimiento', "warning");
      Util::filamentNotification('Este empredimiento ha vencido y no es extendible', "warning");
      return;
    }
    $venture->is_active = ! $venture->is_active;
    $venture->save();

    $state = ($venture->is_active) ? 'Activado' : 'Inactivado';
    $venture->addComment("Emprendimiento {$state}, Memo: {$data['reason']}");
    Mail::to($venture->member)->send(new VentureActiveToggled($venture));
  }
}
