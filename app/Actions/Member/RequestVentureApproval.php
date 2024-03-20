<?php

namespace App\Actions\Member;

use App\Enums\ApprovalState;
use App\Models\Venture;
use Filament\Notifications\Notification;
use Lorisleiva\Actions\Concerns\AsAction;

class RequestVentureApproval
{
  use AsAction;

  public function handle(Venture $venture)
  {
    $venture->approval_state = ApprovalState::PENDING;
    $venture->save();
  }
}
