<?php

namespace App\Models;

use App\Enums\MemberType;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Hash;

class Member extends Authenticatable implements FilamentUser
{
  use HasFactory;

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

  protected function password(): Attribute
  {
    return Attribute::make(
      set: fn (string $value) => Hash::make($value)
    );
  }
}
