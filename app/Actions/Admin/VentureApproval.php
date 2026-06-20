<?php

namespace App\Actions\Admin;

use App\Enums\VentureApprovalState;
use App\Mail\Member\VentureRequestApproved;
use App\Mail\Member\VentureRequestDenied;
use App\Models\Venture;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;

class VentureApproval
{
    use AsAction;

    public function handle(Venture $venture, array $data)
    {
        $state = VentureApprovalState::from($data['decision']);
        if (! in_array($state, [VentureApprovalState::APPROVED, VentureApprovalState::REJECTED])) {
            throw new \Exception(
                message: __('actions/admin.respond-venture-approval-request.exceptions.invalid-state')
            );
        }

        $state === VentureApprovalState::APPROVED
          ? $this->approve($venture, $data['approval_reason'])
          : $this->reject($venture, $data['approval_reason']);

        $venture->approval_by = auth()->user()->name;
        $venture->approval_at = now();
        if ($state === VentureApprovalState::APPROVED) {
            $venture->is_active = true;
        }

        $venture->save();

        if ($venture->approval_state === VentureApprovalState::APPROVED) {
            $venture->addComment("Decisión de aprobación: APROBADO, Memo: {$data['approval_reason']}");
            Mail::to($venture->member)->send(new VentureRequestApproved($venture));
        }
        if ($venture->approval_state === VentureApprovalState::REJECTED) {
            $venture->addComment("Decisión de aprobación: RECHAZADO, Memo: {$data['approval_reason']}");
            Mail::to($venture->member)->send(new VentureRequestDenied($venture));
        }
    }

    protected function approve(Venture $venture, ?string $reason)
    {
        $venture->approval_state = VentureApprovalState::APPROVED;
        $venture->approval_reason = $reason;
    }

    protected function reject(Venture $venture, ?string $reason)
    {
        $venture->approval_state = VentureApprovalState::REJECTED;
        $venture->approval_reason = $reason;
    }
}
