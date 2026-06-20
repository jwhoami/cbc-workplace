<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Helpers\Util;
use App\Models\Organization;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class ReactivateOrganization
{
    use AsAction;

    public function handle(Organization $organization): ReactivateOrganizationResult
    {
        if (! $organization->is_suspended()) {
            return ReactivateOrganizationResult::notSuspended();
        }

        DB::transaction(function () use ($organization): void {
            $organization->suspended_at = null;
            $organization->suspended_by = null;
            $organization->suspension_reason = null;
            $organization->save();

            $organization->addComment(__('actions/admin.reactivate-organization.org-comment'));
        });

        Util::getActivityLog('organization-reactivated')
            ->performedOn($organization)
            ->withProperties([
                'ip' => request()->ip(),
                'organization_id' => $organization->id,
            ])
            ->log(__('actions/admin.reactivate-organization.log-message'));

        return ReactivateOrganizationResult::reactivated();
    }
}
