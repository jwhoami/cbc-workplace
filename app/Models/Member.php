<?php

namespace App\Models;

use App\Enums\MembershipState;
use App\Enums\MemberType;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class Member extends Authenticatable implements FilamentUser, MustVerifyEmail, HasAvatar
{
  use HasFactory;
  use Notifiable;

  protected $guarded = [
    'remember_token',
  ];

  protected $hidden = [
    'password',
    'remember_token',
  ];

  protected $casts = [
    'type' => MemberType::class,
    'membership_state' => MembershipState::class,
    'membership_approval_at' => 'datetime',
    'expires_at' => 'datetime',
    'social_medias' => 'array',
    'is_active' => 'boolean',
    'is_blocked' => 'boolean',
  ];

  protected static function booted(): void
  {
    static::deleting(function (Member $record) {
      $record->ventures->categories()->detach();
      $record->ventures()->media()->delete();
    });
  }

  public function sponsor(): MorphOne
  {
    return $this->morphOne(Invitation::class, 'sponsor');
  }

  public function contact(): HasOne
  {
    return $this->hasOne(MemberContact::class);
  }

  public function invitation(): BelongsTo
  {
    return $this->belongsTo(Invitation::class);
  }

  public function comments(): MorphMany
  {
    return $this->morphMany(Comments::class, 'commentable');
  }

  public function favorites(): HasMany
  {
    return $this->hasMany(Favorite::class);
  }

  public function role()
  {
    return $this->belongsTo(Role::class);
  }

  public function addComment(string $comment)
  {
    $this->comments()->create(['comment' => $comment, 'comment_by' => Filament::auth()->user()->name]);
  }

  public function canAccessPanel(Panel $panel): bool
  {
    $user = Filament::auth()->user();
    $canAccess = $panel->getId() === 'member' && ($user instanceof self) && ($user->is_active && ! $user->is_blocked);
    return $canAccess;
  }

  public function getFilamentAvatarUrl(): ?string
  {
    return "https://ui-avatars.com/api/?name={$this->name}";
    // return $this->avatar
    //   ? Storage::disk('avatars')->url($this->avatar)
    //   : "https://ui-avatars.com/api/?name={$this->name}";
  }

  //  protected function password(): Attribute
  //  {
  //    return Attribute::make(
  //      set: fn (string $value) => Hash::make($value)
  //    );
  //  }

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

  public function hasPermission($uperm)
  {
    $perm = $this->role?->perm;
    if (! $perm) {
      return false;
    }
    $allowed = in_array($uperm, $perm);
    //dd($allowed, $uperm, $perm);
    return $allowed;
  }
}
