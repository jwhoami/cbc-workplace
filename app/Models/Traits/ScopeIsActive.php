<?php

namespace App\Models\Traits;

use Illuminate\Contracts\Database\Eloquent\Builder;

trait ScopeIsActive
{
  public function scopeIsActive(Builder $query, bool $value = true)
  {
    $query->where('is_active', $value);
  }
}
