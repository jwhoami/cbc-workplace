<?php

namespace App\Actions\Member;

use App\Enums\VentureApprovalState;
use App\Helpers\AppUtil;
use App\Mail\Member\AffilateRequest;
use App\Mail\Member\VentureApprovalRequest;
use App\Models\Config;
use App\Models\User;
use App\Models\Venture;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;

class RequestVentureApproval
{
  use AsAction;

  public function handle(Venture $venture)
  {
    $venture->approval_state = VentureApprovalState::APPROVAL;
    $venture->save();

    $venture->addComment('Solicitud de aprobación de emprendimiento');


    $approvers = AppUtil::getActiveUsersInRole("DIACONO");

    foreach ($approvers as $user) {
      Mail::to($user)->send(new VentureApprovalRequest($venture));
    }
  }
}
