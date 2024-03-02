<?php

namespace App\Actions\Member;

use App\Enums\MembershipState;
use App\Models\Member;
use Lorisleiva\Actions\Concerns\AsAction;

class RequestMembership
{
  use AsAction;

  public function handle(Member $member, array $data)
  {
    $member->membership_state = MembershipState::PENDING;
    $member->membership_reason = $data['reason'];
    $member->save();
  }
}
