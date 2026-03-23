<?php

namespace App\Policies;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Model;

class BasePolicy
{
  use HandlesAuthorization;

  public static $filamentPanel = true;
  public static $name = "";

  public function before(Model $user, string $ability): bool|null
  {
    if ($user instanceof User && $user->isAdmin()) {
      return true;
    }

    return null;
  }

  public function viewAny(Model $user)
  {
    return $user->hasPermission(static::prefix());
  }

  public function view(?Model $user)
  {
    return $user->hasPermission(static::prefix());
  }

  public function create(Model $user)
  {
    return $user->hasPermission(static::prefix());
  }

  public function update(Model $user)
  {
    return $user->hasPermission(static::prefix());
  }

  public function delete(Model $user)
  {
    return $user->hasPermission(static::prefix());
  }

  public function deleteAny(Model $user)
  {
    return $user->hasPermission(static::prefix());
  }

  public function restore(Model $user)
  {
    return $user->hasPermission(static::prefix());
  }

  public function forceDelete(Model $user)
  {
    return $user->hasPermission(static::prefix());
  }

  public function toggleflagActive(Model $user)
  {
    return $user->hasPermission(static::prefix());
  }

  public function toggleflagsActive(Model $user)
  {
    return $user->hasPermission(static::prefix());
  }

  public static function prefix($name = null)
  {
    if (! $name) {
      $name = debug_backtrace()[1]['function'];
    }
    $tokens = [
      ucfirst(Filament::getCurrentPanel()->getId()),
      static::$name,
      $name,
    ];
    $tokens = array_filter($tokens);
    $key = implode(".", $tokens);
    return $key;
  }
}
