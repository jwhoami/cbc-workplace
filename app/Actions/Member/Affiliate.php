<?php

namespace App\Actions\Member;

use App\Enums\MembershipState;
use App\Enums\MemberType;
use App\Mail\Member\AffiliateRequestApproved;
use App\Models\Member;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;

class Affiliate
{
  use AsAction;

  public function handle(Member $member): bool
  {
    $member->membership_state = MembershipState::APPROVED;
    $member->type = MemberType::MEMBER;
    $member->save();
    Mail::to($member)->send(new AffiliateRequestApproved($member));

    return true;
  }
}
