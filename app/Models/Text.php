<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Text extends Model
{
  /* Requirements:
   * - \App\Mail\Dynamic
   * - resources.views.mail.dynamic
   *
   * For Filament:
   * - \App\Filament\Resources\MailableResource
   * */
  use HasFactory, LogsActivity;

  protected $guarded = [];

  protected $casts = [];

  public function scopeActive(Builder $query): Builder
  {
    return $query->where('is_active', true);
  }

  public function scopeLatestText(Builder $query, $code): Builder
  {
    return $query
      ->where('code', trim($code))
      ->where('is_active', true)
      ->orderBy('created_at', 'desc');
  }

  public function getActivitylogOptions(): LogOptions
  {
    return LogOptions::defaults()
      ->logUnguarded()
      ->logOnlyDirty();
  }
}
