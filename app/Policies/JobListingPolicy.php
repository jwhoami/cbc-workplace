<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\JobListingState;
use App\Enums\OrganizationVerificationState;
use App\Helpers\Util;
use App\Models\JobListing;
use App\Models\Member;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Model;

class JobListingPolicy extends BasePolicy
{
    public static $name = 'JobListing';

    public function viewAny(Model $user)
    {
        if ($user instanceof Member && Util::isPanelActive('member')) {
            return true;
        }

        return parent::viewAny($user);
    }

    public function view(?Model $user, ?JobListing $jobListing = null)
    {
        if (! $jobListing) {
            return parent::view($user);
        }

        if ($user instanceof Member && Util::isPanelActive('member')) {
            return $this->memberOwnsListing($user, $jobListing);
        }

        return parent::view($user);
    }

    public function create(Model $user): bool
    {
        if ($user instanceof Member && Util::isPanelActive('member')) {
            $organization = Organization::where('member_id', $user->id)->first();

            return $organization
              && $organization->verification_state === OrganizationVerificationState::VERIFIED
              && ! $this->organizationFrozenFor($user, $organization);
        }

        return false;
    }

    public function update(Model $user, ?JobListing $jobListing = null): bool
    {
        if ($user instanceof Member && Util::isPanelActive('member') && $jobListing) {
            if ($this->organizationFrozenFor($user, $jobListing->organization)) {
                return false;
            }

            return $this->memberOwnsListing($user, $jobListing) && $jobListing->canEdit();
        }

        return false;
    }

    public function delete(Model $user, ?JobListing $jobListing = null): bool
    {
        if ($user instanceof Member && Util::isPanelActive('member') && $jobListing) {
            if ($this->organizationFrozenFor($user, $jobListing->organization)) {
                return false;
            }

            return $this->memberOwnsListing($user, $jobListing) && $jobListing->canEdit();
        }

        return false;
    }

    public function close(Model $user, ?JobListing $jobListing = null): bool
    {
        if ($user instanceof Member && Util::isPanelActive('member') && $jobListing) {
            if ($this->organizationFrozenFor($user, $jobListing->organization)) {
                return false;
            }

            return $this->memberOwnsListing($user, $jobListing)
                && $jobListing->state === JobListingState::ACTIVE;
        }

        return false;
    }

    public function submitForApproval(Model $user, ?JobListing $jobListing = null): bool
    {
        if ($user instanceof Member && Util::isPanelActive('member') && $jobListing) {
            if ($this->organizationFrozenFor($user, $jobListing->organization)) {
                return false;
            }

            return $this->memberOwnsListing($user, $jobListing) && $jobListing->canSubmit();
        }

        return false;
    }

    protected function memberOwnsListing(Member $member, JobListing $jobListing): bool
    {
        return $member->id === $jobListing->member_id
            || $member->id === $jobListing->organization?->member_id;
    }

    protected function organizationFrozenFor(Member $member, ?Organization $organization = null): bool
    {
        return (new OrganizationPolicy)->organizationFrozenForMember($member, $organization);
    }
}
