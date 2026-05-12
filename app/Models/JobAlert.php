<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\JobAlertFrequency;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class JobAlert extends Model
{
    use HasFactory, LogsActivity;

    protected $guarded = [];

    protected $casts = [
        'frequency' => JobAlertFrequency::class,
        'active' => 'boolean',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function dispatchLogs(): HasMany
    {
        return $this->hasMany(JobAlertDispatchLog::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comments::class, 'commentable');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function scopeOfMember(Builder $query, Member|int $member): Builder
    {
        $id = is_int($member) ? $member : $member->id;

        return $query->where('member_id', $id);
    }

    public function scopeWithFrequency(Builder $query, JobAlertFrequency $frequency): Builder
    {
        return $query->where('frequency', $frequency->value);
    }

    public function scopeOfActiveMember(Builder $query): Builder
    {
        return $query->whereHas('member', fn (Builder $mq) => $mq->where('is_active', true));
    }

    public function addComment(string $comment): void
    {
        $user = Filament::auth()->user() ?? auth()->user();
        $this->comments()->create([
            'comment' => $comment,
            'comment_by' => $user?->name ?? 'Sistema',
        ]);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['category_id', 'city', 'frequency', 'active'])
            ->logOnlyDirty();
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        $activity->properties = $activity->properties->merge([
            'ip' => request()->ip(),
        ]);
    }
}
