<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class CandidateProfile extends Model
{
  use HasFactory, LogsActivity;

  protected $guarded = [];

  protected $casts = [
    'is_visible' => 'boolean',
  ];

  protected static function booted(): void
  {
    static::deleting(function (CandidateProfile $profile) {
      if ($profile->photo) {
        Storage::disk('public')->delete($profile->photo);
      }
      if ($profile->cv_path) {
        Storage::disk('public')->delete($profile->cv_path);
      }
    });
  }

  public function member(): BelongsTo
  {
    return $this->belongsTo(Member::class);
  }

  public function workExperiences(): HasMany
  {
    return $this->hasMany(WorkExperience::class);
  }

  public function educations(): HasMany
  {
    return $this->hasMany(Education::class);
  }

  public function getActivitylogOptions(): LogOptions
  {
    return LogOptions::defaults()
      ->logOnly(['headline', 'summary', 'city', 'province', 'phone', 'is_visible'])
      ->logOnlyDirty();
  }

  public function tapActivity(Activity $activity, string $eventName)
  {
    $activity->properties = $activity->properties->merge([
      'ip' => request()->ip(),
    ]);
  }
}
