<?php

namespace App\Helpers;

use App\Models\Role;

class AppUtil
{
  public static function getActiveUsersInRole($role)
  {
    $role = Role::query()
      ->with(['users' => function ($query) {
        $query->active();
      }])
      ->where('name', $role)
      ->active()
      ->first();
    return $role->users;
  }
}
