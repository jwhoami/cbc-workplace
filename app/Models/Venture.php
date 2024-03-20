<?php

namespace App\Models;

use App\Enums\ApprovalState;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Venture extends Model
{
  use HasFactory;

  protected $guarded = [];

  protected $casts = [
    'approval_state' => ApprovalState::class,
    'approval_at' => 'datetime',
    'expires_at' => 'datetime',
  ];

  public function member(): BelongsTo
  {
    return $this->belongsTo(Member::class);
  }

  public function scopeOfMember(Builder $query, Member | int $member): void
  {
    $id = is_integer($member) ? $member : $member->id;

    $query->where('member_id', $id);
  }

  public function isApprovalReasonOld(): bool
  {
    return $this->approval_reason && $this->approval_state === ApprovalState::PENDING;
  }

  public function canRequestApproval(): bool
  {
    return in_array($this->approval_state, [ApprovalState::UNDEFINED, ApprovalState::REJECTED]);
  }

  public function canEdit(): bool
  {
    return $this->approval_state === ApprovalState::PENDING;
  }
}
