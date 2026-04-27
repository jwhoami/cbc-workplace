<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
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

class Application extends Model
{
    use HasFactory, LogsActivity;

    protected $guarded = [];

    protected $casts = [
        'status' => ApplicationStatus::class,
        'screening_answers' => 'array',
        'submitted_at' => 'datetime',
        'last_status_changed_at' => 'datetime',
        'anonymized_at' => 'datetime',
    ];

    public function jobListing(): BelongsTo
    {
        return $this->belongsTo(JobListing::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function candidateProfile(): BelongsTo
    {
        return $this->belongsTo(CandidateProfile::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ApplicationNote::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comments::class, 'commentable');
    }

    public function addComment(string $comment): void
    {
        $user = Filament::auth()->user() ?? auth()->user();
        $this->comments()->create(['comment' => $comment, 'comment_by' => $user?->name ?? 'Sistema']);
    }

    public function scopeOfMember(Builder $query, Member|int $member): void
    {
        $id = is_int($member) ? $member : $member->id;
        $query->where('member_id', $id);
    }

    public function scopeOfOrganization(Builder $query, Organization|int $organization): void
    {
        $id = is_int($organization) ? $organization : $organization->id;
        $query->whereHas('jobListing', fn (Builder $q) => $q->where('organization_id', $id));
    }

    public function scopeWithStatus(Builder $query, ApplicationStatus $status): void
    {
        $query->where('status', $status);
    }

    public function isAnonymized(): bool
    {
        return $this->anonymized_at !== null;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'last_status_changed_by', 'anonymized_at'])
            ->logOnlyDirty();
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->properties = $activity->properties->merge([
            'ip' => request()->ip(),
        ]);
    }
}
