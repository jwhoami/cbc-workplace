<?php

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
            return $user->id === $organization->member_id;
        }

        return $user->hasPermission(static::prefix());
    }

    public function verify(Model $user)
    {
        return $user->hasPermission(static::prefix());
    }

    public function suspend(Model $user)
    {
        return $user->hasPermission(static::prefix());
    }
}
