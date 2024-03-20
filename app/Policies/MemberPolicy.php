<?php

namespace App\Policies;

use App\Enums\MembershipState;
use App\Models\Member;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;

class MemberPolicy
{
  public function requestMembership(Model $user, Member $member): bool
  {
    if ($user instanceof User) return false;

    $validStates = [MembershipState::UNDEFINED, MembershipState::REJECTED];
    if (!in_array($member->membership_state, $validStates)) return false;

    return true;
  }

  public function viewMembershipRequest(Model $user, Member $member): bool
  {
    if (!($user instanceof User)) return false;
  }
}
