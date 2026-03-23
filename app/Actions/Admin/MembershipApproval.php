<?php

namespace App\Actions\Admin;

use App\Enums\MembershipState;
use App\Enums\MemberType;
use App\Mail\Member\AffiliateRequestApproved;
use App\Mail\Member\AffiliateRequestDenied;
use App\Models\Member;
use App\Models\Role;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;

class MembershipApproval
{
  use AsAction;

  public function handle(Member $member, array $data)
  {
    $state = MembershipState::from($data['decision']);
    if ($state !== MembershipState::APPROVED && $state !== MembershipState::REJECTED) {
      throw new \Exception(
        message: __('actions/admin.membership-approval.exceptions.invalid-state')
      );
    }

    $state === MembershipState::APPROVED
      ? $this->approve($member, $data['membership_approval_reason'])
      : $this->reject($member, $data['membership_approval_reason']);

    $member->membership_approval_by = auth()->user()->name;
    $member->membership_approval_at = now();
    $member->save();

    if ($member->membership_state === MembershipState::APPROVED) {
      $member->addComment('Afiliación Aprobada: ' . $data['membership_approval_reason']);
      Mail::to($member)->send(new AffiliateRequestApproved($member));
    }
    if ($member->membership_state === MembershipState::REJECTED) {
      $member->addComment('Afiliación Rechazada:' . $data['membership_approval_reason']);
      Mail::to($member)->send(new AffiliateRequestDenied($member));
    }
  }

  protected function approve(Member $member, ?string $reason = null)
  {
    $member->type = MemberType::MEMBER;
    $member->membership_state = MembershipState::APPROVED;
    $member->membership_approval_reason = $reason;
    $member->role()->associate(Role::where('name', 'AFILIADO')->first());
  }

  protected function reject(Member $member, ?string $reason = null)
  {
    $member->membership_state = MembershipState::REJECTED;
    $member->membership_approval_reason = $reason;
    $member->role_id = null;
  }
}
