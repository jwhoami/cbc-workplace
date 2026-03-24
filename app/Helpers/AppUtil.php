<?php

namespace App\Helpers;

use App\Models\Role;
use App\Models\User;

class AppUtil
{
    public static function getActiveUsersInRole($role)
    {
        $role = Role::query()
            ->with([
                'users' => function ($query) {
                    $query->active();
                },
            ])
            ->where('name', $role)
            ->active()
            ->first();

        return $role->users;
    }

    public static function getVentureApprovers()
    {
        $users = User::query()
            ->where('can_approve', 1)
            ->active()
            ->get();

        return $users;
    }

    public static function getAffiliateApprovers()
    {
        $users = User::query()
            ->where('can_approve', 1)
            ->active()
            ->get();

        return $users;
    }

    public static function getJobListingApprovers()
    {
        $users = User::query()
            ->where('can_sponsor', 1)
            ->active()
            ->get();

        return $users;
    }
}
