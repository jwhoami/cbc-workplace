<?php

namespace App\Actions\Member;

use App\Enums\OrganizationVerificationState;
use App\Helpers\AppUtil;
use App\Helpers\Util;
use App\Mail\Organization\VerificationRequested;
use App\Models\Organization;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;

class RequestOrganizationVerification
{
    use AsAction;

    public function handle(Organization $organization)
    {
        if ($organization->verification_state === OrganizationVerificationState::VERIFIED) {
            throw new \Exception(
                message: __('actions/member.request-organization-verification.exceptions.already-verified')
            );
        }

        if ($organization->verification_state === OrganizationVerificationState::SUSPENDED) {
            $organization->verification_state = OrganizationVerificationState::PENDING;
            $organization->verification_reason = null;
            $organization->save();
        }

        $organization->addComment('Verificación solicitada por '.auth()->user()->name);

        Util::getActivityLog('organization-verification-requested')
            ->performedOn($organization)
            ->log('Solicitud de verificación de organización');

        $approvers = AppUtil::getActiveUsersInRole('admin');

        foreach ($approvers as $user) {
            Mail::to($user)->send(new VerificationRequested($organization));
        }
    }
}
