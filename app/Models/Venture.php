<?php

namespace App\Models;

use App\Enums\VentureApprovalState;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Storage;

class Venture extends Model
{
  use HasFactory;

  protected $guarded = [];

  protected $casts = [
    'approval_state' => VentureApprovalState::class,
    'approval_at' => 'datetime',
    'expires_at' => 'datetime',
    'is_expired' => 'boolean',
    'is_active' => 'boolean',
    'is_extendable' => 'boolean',
  ];

  protected static function booted(): void
  {
    static::deleting(function (Venture $record) {
      if ($file = $record->file) {
        Storage::disk('public')->delete($file);
      }
    });
  }

  public function member(): BelongsTo
  {
    return $this->belongsTo(Member::class);
  }

  public function comments(): MorphMany
  {
    return $this->morphMany(Comments::class, 'commentable');
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
    return $this->approval_reason && $this->approval_state === VentureApprovalState::PENDING;
  }

  public function canRequestApproval(): bool
  {
    return in_array($this->approval_state, [VentureApprovalState::UNDEFINED, VentureApprovalState::REJECTED]);
  }

  public function canEdit(): bool
  {
    return $this->approval_state === VentureApprovalState::PENDING;
  }

  public function scopeActive(Builder $query): Builder
  {
    return $query->where('is_active', 1);
  }
}
