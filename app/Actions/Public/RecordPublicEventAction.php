<?php

declare(strict_types=1);

namespace App\Actions\Public;

use App\Enums\PublicEventKind;
use App\Enums\VisitorVariant;
use App\Models\PublicEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Persists a single observability event for the public job-board surface (FR-031).
 *
 * Privacy: callers MUST sanitize raw keyword content out of the payload before invoking
 * this action — the action does NOT inspect the payload for PII; it persists what it's
 * given. For KeywordQuery events the caller passes only the diacritic-folded form of the
 * keyword plus the raw length.
 */
class RecordPublicEventAction
{
    use AsAction;

    public function handle(
        PublicEventKind $kind,
        Request $request,
        VisitorVariant $variant,
        ?int $pageNumber = null,
        ?array $payload = null,
        ?string $correlationId = null,
    ): PublicEvent {
        return PublicEvent::create([
            'kind' => $kind,
            'correlation_id' => $correlationId ?? (string) Str::uuid(),
            'occurred_at' => now(),
            'path' => $request->path() === '/' ? '/' : '/'.ltrim($request->path(), '/'),
            'query_string' => $request->getQueryString(),
            'visitor_variant' => $variant->value,
            'page_number' => $pageNumber,
            'payload' => $payload,
        ]);
    }
}
