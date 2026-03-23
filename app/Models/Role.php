<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
  use HasFactory, LogsActivity;

  public $fillable = [
    'name',
    'title',
    'is_active',
    'is_admin',
  ];

  public $casts = [
    'perm' => 'array',
  ];

  public function users()
  {
    return $this->hasMany(User::class);
  }

  public function isAuthorized($user, $request)
  {
    $perm = $user->Role->perm;
    if (!$perm) return false;
    $allow = in_array("*.*", $perm);
    if ($allow) return true;

    $routeName = $request->route()->getName();
    if (!$routeName) return false;

    // dd($routeName);
    $allow = in_array($routeName, $perm);
    return $allow;
  }

  public function hasPermission($user, $uperm)
  {
    $perm = $user->Role->perm;
    // dump($uperm);
    // dd($perm);
    if (!$perm) return false;
    $allowed =  in_array($uperm, $perm);
    return $allowed;
  }

  public function scopeActive($query, $state = true)
  {
    $query->where('is_active', $state);
  }

  public function scopeAdmin($query, $state = true)
  {
    $query->where('is_admin', $state);
  }

  public function getActivitylogOptions(): LogOptions
  {
    return LogOptions::defaults();
  }
}
