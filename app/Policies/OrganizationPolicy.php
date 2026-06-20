<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Member;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Model;

class OrganizationPolicy extends BasePolicy
{
    public static $name = 'Organization';

    public function update(Model $user, ?Organization $organization = null)
    {
        if ($user instanceof Member && $organization) {
            if ($this->organizationFrozenForMember($user, $organization)) {
                return false;
            }

            return $user->id === $organization->member_id;
        }

        return $user->hasPermission(static::prefix());
    }

    public function verify(Model $user)
    {
        return $user->hasPermission(static::prefix());
    }

    public function suspend(Model $user, ?Organization $organization = null)
    {
        if ($organization && ! $organization->canBeSuspended()) {
            return false;
        }

        return $user->hasPermission(static::prefix());
    }

    public function reactivate(Model $user, ?Organization $organization = null)
    {
        if ($organization && ! $organization->canBeReactivated()) {
            return false;
        }

        return $user->hasPermission(static::prefix());
    }

    /**
     * Shared helper consumed by JobListingPolicy / ApplicationPolicy / etc.
     * Returns true when the member's organization is currently suspended
     * — the caller should deny any mutation in that case.
     */
    public function organizationFrozenForMember(Member $member, ?Organization $organization = null): bool
    {
        $org = $organization ?? $member->organization;

        return (bool) ($org?->is_suspended());
    }
}
