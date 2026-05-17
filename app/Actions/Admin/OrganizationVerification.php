<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Enums\OrganizationVerificationState;
use App\Helpers\Util;
use App\Mail\Organization\Verified;
use App\Models\Organization;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;

class OrganizationVerification
{
    use AsAction;

    /**
     * Spec 009 §R1: this action no longer handles suspension. The SUSPENDED
     * branch has been removed; the orthogonal suspension flag is owned by
     * {@see SuspendOrganization} / {@see ReactivateOrganization}.
     */
    public function handle(Organization $organization, array $data)
    {
        $decision = OrganizationVerificationState::from($data['decision']);

        if ($decision !== OrganizationVerificationState::VERIFIED) {
            throw new \Exception(
                message: __('actions/admin.organization-verification.exceptions.invalid-decision')
            );
        }

        $this->verify($organization);

        $organization->verification_by = auth()->user()->name;
        $organization->verified_at = now();
        $organization->save();

        Util::getActivityLog('organization-verified')
            ->performedOn($organization)
            ->log('Organización verificada');
    }

    protected function verify(Organization $organization): void
    {
        $organization->verification_state = OrganizationVerificationState::VERIFIED;
        $organization->verification_reason = null;
        $organization->is_active = true;

        $organization->addComment('Organización verificada');

        Mail::to($organization->member)->send(new Verified($organization));
    }
}
