<?php

namespace App\Models;

use App\Enums\MemberType;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class Member extends Authenticatable implements FilamentUser, MustVerifyEmail, HasAvatar
{
  use HasFactory, Notifiable;

  protected $guarded = [
    'remember_token'
  ];

  protected $hidden = [
    'password',
    'remember_token',
  ];

  protected $casts = [
    'type' => MemberType::class,
    'social_medias' => 'array',
  ];

  public function canAccessPanel(Panel $panel): bool
  {
    return $panel->getId() === 'member';
  }

  public function getFilamentAvatarUrl(): ?string
  {
    return $this->avatar;
  }

  protected function password(): Attribute
  {
    return Attribute::make(
      set: fn (string $value) => Hash::make($value)
    );
  }
}
