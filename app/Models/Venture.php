<?php

namespace App\Models;

use App\Enums\VentureApprovalState;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Storage;

class Venture extends Model
{
  use HasFactory;

  protected $guarded = [];

  protected array $fileFields = [
    'disk' => 'public',
    'file',
  ];

  protected $casts = [
    'approval_state' => VentureApprovalState::class,
    'approval_at' => 'datetime',
    'expires_at' => 'datetime',
    'preview_until' => 'datetime',
    'is_expired' => 'boolean',
    'is_active' => 'boolean',
    'is_extendable' => 'boolean',
  ];

  protected static function booted(): void
  {
    static::deleting(function (Venture $record) {
      if ($record->file) {
        Storage::disk('public')->delete($record->file);
      }
      $record->media->each(function (Media $media) {
        $media->delete();
      });
      $record->categories()->detach();
    });
  }

  public function member(): BelongsTo
  {
    return $this->belongsTo(Member::class);
  }

  public function favorites(): HasMany
  {
    return $this->hasMany(Favorite::class);
  }

  public function comments(): MorphMany
  {
    return $this->morphMany(Comments::class, 'commentable');
  }

  public function media(): MorphMany
  {
    return $this->morphMany(Media::class, 'ownable');
  }

  public function addComment(string $comment)
  {
    $this->comments()->create(['comment' => $comment, 'comment_by' => Filament::auth()->user()->name]);
  }

  public function categories(): MorphToMany
  {
    return $this->morphToMany(Category::class, 'categorizable');
  }

  public function scopeOfMember(Builder $query, Member | int $member): void
  {
    $id = is_int($member) ? $member : $member->id;

    $query->where('member_id', $id);
  }

  public function isApprovalReasonOld(): bool
  {
    return $this->approval_reason && $this->approval_state === VentureApprovalState::APPROVAL;
  }

  public function canRequestApproval(): bool
  {
    return in_array($this->approval_state, [VentureApprovalState::APPROVAL, VentureApprovalState::REJECTED]);
  }

  public function canEdit(): bool
  {
    return $this->approval_state === VentureApprovalState::APPROVAL;
  }

  public function scopeActive(Builder $query): Builder
  {
    return $query->where('is_active', 1);
  }

  public function resetApproval(bool $autoSave = false): void
  {
    if ($this->approval_state == VentureApprovalState::REJECTED) {
      $this->approval_state = VentureApprovalState::UPDATED;
    }
    $this->approval_by = null;
    $this->approval_at = null;
    $this->approval_reason = null;
    $this->is_active = false;
    $this->preview_until = null;

    if ($autoSave) {
      $this->save();
    }
  }

  public function isExpired(): bool
  {
    return ($this->expires_at?->lessThan(now()) || $this->is_expired);
  }

  public function updateViewCount(): void
  {
    $this->view_count++;
    $this->save();
  }

  public function updateFavoriteCount(): void
  {
    $this->favorite_count = $this->favorites->count();
    $this->save();
  }
}
