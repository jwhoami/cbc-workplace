<?php

namespace App\Actions;

use App\Enums\ApprovalState;
use App\Models\Member;
use Lorisleiva\Actions\Concerns\AsAction;

class RequestMembership
{
  use AsAction;

  public function handle(Member $member, array $data)
  {
    $member->approval_state = ApprovalState::PENDING;
    $member->approval_reason = $data['reason'];
    $member->save();
  }
}
