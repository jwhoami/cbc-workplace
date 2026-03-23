<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Favorite extends Model
{
  use HasFactory;

  protected $guarded = [];

  public function member(): BelongsTo
  {
    return $this->belongsTo(Member::class);
  }

  public function venture(): BelongsTo
  {
    return $this->belongsTo(Venture::class);
  }
}
