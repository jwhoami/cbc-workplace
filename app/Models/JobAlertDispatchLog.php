<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DispatchDecision;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobAlertDispatchLog extends Model
{
    use HasFactory;

    protected $guarded = [];

    public $timestamps = false;

    protected $casts = [
        'matched_offer_ids' => 'array',
        'dispatched_at' => 'datetime',
        'decision' => DispatchDecision::class,
    ];

    public function jobAlert(): BelongsTo
    {
        return $this->belongsTo(JobAlert::class);
    }
}
