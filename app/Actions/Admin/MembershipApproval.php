<?php

namespace App\Actions\Admin;

use App\Enums\MembershipState;
use App\Enums\MemberType;
use App\Models\Member;
use Lorisleiva\Actions\Concerns\AsAction;

class MembershipApproval
{
  use AsAction;

  public function handle(Member $member, array $data)
  {
    $state = MembershipState::from($data['decision']);
    if ($state !== MembershipState::APPROVED && $state !== MembershipState::REJECTED) throw new \Exception(
      message: __('actions/admin.membership-approval.exceptions.invalid-state')
    );

    $state === MembershipState::APPROVED
      ? $this->approve($member, $data['membership_approval_reason'])
      : $this->reject($member, $data['membership_approval_reason']);

    $member->membership_approval_by = auth()->user()->name;
    $member->membership_approval_at = now();
    $member->save();
  }

  protected function approve(Member $member, string $reason)
  {
    $member->type = MemberType::MEMBER;
    $member->membership_state = MembershipState::APPROVED;
    $member->membership_approval_reason = $reason;
  }

  protected function reject(Member $member, string $reason)
  {
    $member->membership_state = MembershipState::REJECTED;
    $member->membership_approval_reason = $reason;
  }
}
