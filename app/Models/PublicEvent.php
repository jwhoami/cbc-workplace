<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PublicEventKind;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Append-only observability event emitted by the public job-board surface
 * (FR-031). Spec 009 will read this table for analytics dashboards. Never
 * updated after insert; retention is owned by spec 009.
 */
class PublicEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'kind',
        'correlation_id',
        'occurred_at',
        'path',
        'query_string',
        'visitor_variant',
        'page_number',
        'payload',
    ];

    protected $casts = [
        'kind' => PublicEventKind::class,
        'occurred_at' => 'datetime',
        'payload' => 'array',
    ];
}
