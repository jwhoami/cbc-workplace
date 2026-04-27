<?php

namespace App\Models;

use App\Enums\ContractType;
use App\Enums\JobListingState;
use App\Enums\WorkModality;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class JobListing extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'title',
        'description',
        'requirements',
        'contract_type',
        'work_modality',
        'city',
        'province',
        'salary_min',
        'salary_max',
        'currency',
        'application_deadline',
        'screening_questions',
    ];

    protected $casts = [
        'state' => JobListingState::class,
        'contract_type' => ContractType::class,
        'work_modality' => WorkModality::class,
        'application_deadline' => 'date',
        'published_at' => 'datetime',
        'approval_at' => 'datetime',
        'closed_at' => 'datetime',
        'screening_questions' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (JobListing $listing) {
            if (empty($listing->slug)) {
                $listing->slug = static::generateUniqueSlug($listing->title);
            }
        });

        static::updating(function (JobListing $listing) {
            if ($listing->isDirty('title') && in_array($listing->state, [JobListingState::DRAFT, JobListingState::REJECTED])) {
                $listing->slug = static::generateUniqueSlug($listing->title, $listing->id);
            }
        });
    }

    public static function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($excludeId, fn (Builder $q) => $q->where('id', '!=', $excludeId))
            ->exists()
        ) {
            $slug = "{$originalSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function categories(): MorphToMany
    {
        return $this->morphToMany(Category::class, 'categorizable');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comments::class, 'commentable');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
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
        $query->where('organization_id', $id);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('state', JobListingState::ACTIVE);
    }

    public function canEdit(): bool
    {
        return in_array($this->state, [JobListingState::DRAFT, JobListingState::REJECTED]);
    }

    public function canSubmit(): bool
    {
        return in_array($this->state, [JobListingState::DRAFT, JobListingState::REJECTED]);
    }

    public function isExpired(): bool
    {
        return $this->state === JobListingState::EXPIRED
          || ($this->state === JobListingState::ACTIVE && $this->application_deadline?->isPast());
    }

    public function updateViewCount(): void
    {
        $this->increment('view_count');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'state', 'contract_type', 'work_modality', 'application_deadline'])
            ->logOnlyDirty();
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        $activity->properties = $activity->properties->merge([
            'ip' => request()->ip(),
        ]);
    }
}
