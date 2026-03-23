<?php

namespace App\Actions\Admin;

use App\Enums\OrganizationVerificationState;
use App\Helpers\Util;
use App\Mail\Organization\Suspended;
use App\Mail\Organization\Verified;
use App\Models\Organization;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;

class OrganizationVerification
{
  use AsAction;

  public function handle(Organization $organization, array $data)
  {
    $decision = OrganizationVerificationState::from($data['decision']);

    if (! in_array($decision, [OrganizationVerificationState::VERIFIED, OrganizationVerificationState::SUSPENDED])) {
      throw new \Exception(
        message: __('actions/admin.organization-verification.exceptions.invalid-decision')
      );
    }

    $decision === OrganizationVerificationState::VERIFIED
      ? $this->verify($organization)
      : $this->suspend($organization, $data['verification_reason'] ?? '');

    $organization->verification_by = auth()->user()->name;
    $organization->verified_at = now();
    $organization->save();

    Util::getActivityLog($decision === OrganizationVerificationState::VERIFIED ? 'organization-verified' : 'organization-suspended')
      ->performedOn($organization)
      ->log($decision === OrganizationVerificationState::VERIFIED ? 'Organización verificada' : 'Organización suspendida');
  }

  protected function verify(Organization $organization): void
  {
    $organization->verification_state = OrganizationVerificationState::VERIFIED;
    $organization->verification_reason = null;
    $organization->is_active = true;

    $organization->addComment('Organización verificada');

    Mail::to($organization->member)->send(new Verified($organization));
  }

  protected function suspend(Organization $organization, string $reason): void
  {
    $organization->verification_state = OrganizationVerificationState::SUSPENDED;
    $organization->verification_reason = $reason;
    $organization->is_active = false;

    $organization->addComment("Organización suspendida. Motivo: {$reason}");

    Mail::to($organization->member)->send(new Suspended($organization, $reason));
  }
}
