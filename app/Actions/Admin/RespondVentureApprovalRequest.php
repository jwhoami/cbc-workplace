<?php

namespace App\Actions\Admin;

use App\Enums\ApprovalState;
use App\Models\Venture;
use Lorisleiva\Actions\Concerns\AsAction;

class RespondVentureApprovalRequest
{
  use AsAction;

  public function handle(Venture $venture, array $data)
  {
    $state = ApprovalState::from($data['decision']);
    if (!in_array($state, [ApprovalState::APPROVED, ApprovalState::REJECTED])) throw new \Exception(
      message: __('actions/admin.respond-venture-approval-request.exceptions.invalid-state')
    );

    $state === ApprovalState::APPROVED
      ? $this->approve($venture, $data['approval_reason'])
      : $this->reject($venture, $data['approval_reason']);

    $venture->approval_by = auth()->user()->name;
    $venture->approval_at = now();
    $venture->save();
  }

  protected function approve(Venture $venture, string | null $reason)
  {
    $venture->approval_state = ApprovalState::APPROVED;
    $venture->approval_reason = $reason;
  }

  protected function reject(Venture $venture, string | null $reason)
  {
    $venture->approval_state = ApprovalState::REJECTED;
    $venture->approval_reason = $reason;
  }
}
