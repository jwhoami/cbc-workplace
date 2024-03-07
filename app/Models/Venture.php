<?php

namespace App\Models;

use App\Enums\ApprovalState;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venture extends Model
{
  use HasFactory;

  protected $guarded = [];

  protected $casts = [
    'approval_state' => ApprovalState::class,
    'approval_at' => 'datetime',
  ];
}
