<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
  use HasApiTokens, HasFactory, Notifiable;

  protected $fillable = [
    'name',
    'username',
    'role_id',
    'email',
    'password',
  ];

  protected $hidden = [
    'password',
    'remember_token',
  ];

  protected $casts = [
    'email_verified_at' => 'datetime',
  ];

  public function setPasswordAttribute($value)
  {
    $this->attributes['password'] = Hash::make($value);
  }

  public function role()
  {
    return $this->belongsTo(Role::class);
  }

  public function canAccessPanel(Panel $panel): bool
  {
    return $panel->getId() === 'admin';
  }

  public function scopeActive($query, $state = true)
  {
    $query->where('is_active', $state);
  }

  public function scopeBlocked($query, $state = true)
  {
    $query->where('is_blocked', $state);
  }

  public function hasPermission($uperm)
  {
    $perm = $this->Role?->perm;
    $isAdminRole = (bool) ($this->Role?->is_admin ?? false);
    // if ($uperm == "set-password") {
    //   dump($uperm);
    //   dd($perm);
    // }
    // dd($isAdminRole, $uperm, $perm);
    if ($isAdminRole) return true;
    if (!$perm) return false;
    $allowed =  in_array($uperm, $perm);
    // dd($allowed, $uperm, $perm);
    return $allowed;
  }

  public function hasAnyPermission(array $permissions): bool
  {
    foreach ($permissions as $permission) {
      if ($this->hasPermission($permission)) return true;
    }

    return false;
  }

  public function isAdmin()
  {
    if ($this->Role?->is_admin) return true;
  }

  public function getFilamentAvatarUrl(): ?string
  {
    $url = ($this->profile_photo_path) ? Storage::url($this->profile_photo_path) : null;
    return $url;
  }

  public function lastLogin($ip)
  {
    $this->last_login_ip = $ip;
    $this->last_login_at = now();
    $this->save();
  }
}
