<?php

declare(strict_types=1);

namespace App\Policies;

use App\Helpers\Util;
use App\Models\Application;
use App\Models\Member;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Model;

class ApplicationPolicy extends BasePolicy
{
    public static $name = 'Application';

    public function viewAny(Model $user)
    {
        if ($user instanceof Member && Util::isPanelActive('member')) {
            return true;
        }

        return parent::viewAny($user);
    }

    public function view(?Model $user, ?Application $application = null)
    {
        if (! $application) {
            return parent::view($user);
        }

        if ($user instanceof Member && Util::isPanelActive('member')) {
            if ($user->id === $application->member_id) {
                return true;
            }

            if ($user->id === $application->jobListing->member_id) {
                return true;
            }

            return false;
        }

        return parent::view($user);
    }

    public function create(Model $user)
    {
        if ($user instanceof Member && Util::isPanelActive('member')) {
            if ($this->organizationFrozenFor($user)) {
                return false;
            }

            return $user->candidateProfile()->exists();
        }

        return false;
    }

    public function update(Model $user, ?Application $application = null)
    {
        if ($user instanceof Member && Util::isPanelActive('member') && $application) {
            if ($this->organizationFrozenFor($user, $application->jobListing?->organization)) {
                return false;
            }

            return $user->id === $application->jobListing->member_id;
        }

        return false;
    }

    public function delete(Model $user, ?Application $application = null)
    {
        return false;
    }

    protected function organizationFrozenFor(Member $member, ?Organization $organization = null): bool
    {
        return (new OrganizationPolicy)->organizationFrozenForMember($member, $organization);
    }
}
