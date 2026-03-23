<?php

namespace App\Policies;

use App\Enums\MembershipState;
use App\Models\Member;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;

class MemberPolicy extends BasePolicy
{
  public static $name = "Member";

  public function requestMembership(Model $user, Member $member): bool
  {
    if ($user instanceof User) return false;

    $validStates = [MembershipState::UNDEFINED, MembershipState::REJECTED];
    if (!in_array($member->membership_state, $validStates)) return false;

    return true;
  }

  public function approveMembershipRequest(Model $user, Member $member): bool
  {
    if (!($user instanceof User)) return false;
    if (!$user->hasPermission(static::prefix("approve-membership"))) return false;;
    if ($member->membership_state !== MembershipState::PENDING) return false;

    return true;
  }
}
