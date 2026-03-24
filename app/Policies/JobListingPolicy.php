<?php

namespace App\Policies;

use App\Enums\OrganizationVerificationState;
use App\Helpers\Util;
use App\Models\JobListing;
use App\Models\Member;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Model;

class JobListingPolicy extends BasePolicy
{
    public static $name = 'JobListing';

    public function create(Model $user): bool
    {
        if ($user instanceof Member && Util::isPanelActive('member')) {
            $organization = Organization::where('member_id', $user->id)->first();

            return $organization
              && $organization->verification_state === OrganizationVerificationState::VERIFIED;
        }

        return false;
    }

    public function update(Model $user, ?JobListing $jobListing = null): bool
    {
        if ($user instanceof Member && Util::isPanelActive('member') && $jobListing) {
            return $user->id === $jobListing->member_id && $jobListing->canEdit();
        }

        return false;
    }

    public function delete(Model $user, ?JobListing $jobListing = null): bool
    {
        if ($user instanceof Member && Util::isPanelActive('member') && $jobListing) {
            return $user->id === $jobListing->member_id && $jobListing->canEdit();
        }

        return false;
    }
}
