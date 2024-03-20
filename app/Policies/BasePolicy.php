<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BasePolicy
{
  use HandlesAuthorization;
  public static $name = "";

  public function before(User $user, string $ability): bool|null
  {
    if ($user->isAdmin()) {
      return true;
    }

    return null;
  }

  public function viewAny(User $user)
  {
    return $user->hasPermission(static::prefix("viewAny"));
  }

  public function view(User $user)
  {
    return $user->hasPermission(static::prefix("view"));
  }

  public function create(User $user)
  {
    return $user->hasPermission(static::prefix("create"));
  }

  public function update(User $user)
  {
    return $user->hasPermission(static::prefix("update"));
  }

  public function delete(User $user)
  {
    return $user->hasPermission(static::prefix("delete"));
  }

  public function deleteAny(User $user)
  {
    return $user->hasPermission(static::prefix("deleteAny"));
  }

  public function restore(User $user)
  {
    return $user->hasPermission(static::prefix("restore"));
  }

  public function forceDelete(User $user)
  {
    return $user->hasPermission(static::prefix("destroy"));
  }

  public function toggleflagActive(User $user)
  {
    return $user->hasPermission(static::prefix("toggleflagActive"));
  }

  public function toggleflagsActive(User $user)
  {
    return $user->hasPermission(static::prefix("toggleflagsActive"));
  }

  public static function prefix($name)
  {
    return ucfirst(static::$name) . ".{$name}";
  }
}
