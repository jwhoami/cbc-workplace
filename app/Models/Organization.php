<?php

namespace App\Models;

use App\Enums\OrganizationType;
use App\Enums\OrganizationVerificationState;
use Filament\Facades\Filament;
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['legal_name', 'display_name', 'type', 'verification_state', 'is_active'])
            ->logOnlyDirty();
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->properties = $activity->properties->merge([
            'ip' => request()->ip(),
        ]);
    }
}
