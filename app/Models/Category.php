<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class Category extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'slug', 'icon', 'scope', 'order'])
            ->logOnlyDirty();
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->properties = $activity->properties->merge([
            'ip' => request()->ip(),
        ]);
    }

    protected $guarded = [];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function ventures(): MorphToMany
    {
        return $this->morphedByMany(Venture::class, 'categorizable');
    }

    protected static function booted(): void
    {
        static::created(function (Category $record) {
            if ($record->parent_id !== null) {
                static::updateParentChildCount($record);
            }
        });
        static::deleting(function (Category $record) {
            if ($record->parent_id === null) {
                static::deleteChildren($record);
            }
        });
        static::deleted(function (Category $record) {
            if ($record->parent_id !== null) {
                static::updateParentChildCount($record);
            }
        });
    }

    public static function updateChildCount()
    {
        Category::query()
            ->whereNull('parent_id')
            ->each(function (Category $record) {
                $record->child_count = static::getChildCount($record);
                $record->save();
            });
    }

    public static function updateParentChildCount($record)
    {
        $parent = $record->parent;
        $parent->child_count = static::getChildCount($parent);
        $parent->save();
    }

    public static function getChildCount($record)
    {
        $count = Category::query()
            ->where('parent_id', $record->id)
            ->count();

        return $count;
    }

    public static function deleteChildren($record)
    {
        if ($record->parent_id !== null) {
            return;
        }
        Category::where('parent_id', $record->id)->delete();
    }
}
