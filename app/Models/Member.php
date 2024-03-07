<?php

namespace App\Models;

use App\Enums\MembershipState;
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
use Illuminate\Support\Facades\Storage;

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
    'membership_state' => MembershipState::class,
    'membership_approval_at' => 'datetime',
    'social_medias' => 'array',
  ];

  public function canAccessPanel(Panel $panel): bool
  {
    return $panel->getId() === 'member';
  }

  public function getFilamentAvatarUrl(): ?string
  {
    return $this->avatar
      ? Storage::disk('avatars')->url($this->avatar)
      : "https://ui-avatars.com/api/?name={$this->name}";
  }

  protected function password(): Attribute
  {
    return Attribute::make(
      set: fn (string $value) => Hash::make($value)
    );
  }

  public function canRequestMembership(): bool
  {
    return in_array(
      $this->membership_state,
      [MembershipState::UNDEFINED, MembershipState::REJECTED]
    );
  }

  public function canViewMembershipRequest(): bool
  {
    return $this->membership_state !== MembershipState::UNDEFINED;
  }

  public function isMembershipApprovalRespondeOld(): bool
  {
    $pending = $this->membership_state === MembershipState::PENDING;

    return $this->membership_approval_reason && $pending;
  }
}
