<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrganizationType;
use App\Enums\OrganizationVerificationState;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class Organization extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'legal_name',
        'display_name',
        'type',
        'denomination',
        'description',
        'culture_statement',
        'logo',
        'website',
        'email_contact',
        'phone',
        'city',
        'province',
        'country',
    ];

    protected $casts = [
        'type' => OrganizationType::class,
        'verification_state' => OrganizationVerificationState::class,
        'verified_at' => 'datetime',
        'is_active' => 'boolean',
        'suspended_at' => 'datetime',
    ];

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
        $user = Filament::auth()->user() ?? auth()->user();
        $this->comments()->create(['comment' => $comment, 'comment_by' => $user?->name ?? 'Sistema']);
    }

    public function is_suspended(): bool
    {
        return $this->suspended_at !== null;
    }

    public function canBeSuspended(): bool
    {
        return ! $this->is_suspended();
    }

    public function canBeReactivated(): bool
    {
        return $this->is_suspended();
    }

    public function profileShouldHidePublicData(): bool
    {
        return $this->is_suspended();
    }

    public function scopeExcludingSuspended(Builder $query): Builder
    {
        return $query->whereNull('suspended_at');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'legal_name',
                'display_name',
                'type',
                'verification_state',
                'is_active',
                'suspended_at',
                'suspended_by',
                'suspension_reason',
            ])
            ->logOnlyDirty();
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->properties = $activity->properties->merge([
            'ip' => request()->ip(),
        ]);
    }
}
