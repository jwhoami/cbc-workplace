<?php

declare(strict_types=1);

namespace App\Actions\Public;

use App\Enums\JobListingState;
use App\Models\JobListing;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Resolves a public detail-page slug to one of three states required by
 * FR-018:
 *
 *   STATUS_OK    (200) — slug exists and the offer is active + visible
 *   STATUS_GONE  (410) — slug exists but the offer is no longer active
 *                       (expired, unpublished, or org-hidden); search
 *                       engines can deindex cleanly
 *   STATUS_NOT_FOUND (404) — slug never existed
 *
 * The split between 410 and 404 is what gives crawlers the right signal —
 * 404 means "try later", 410 means "gone forever".
 */
class ResolveOfferBySlugAction
{
    use AsAction;

    public const STATUS_OK = 200;

    public const STATUS_GONE = 410;

    public const STATUS_NOT_FOUND = 404;

    /**
     * @return array{offer: ?JobListing, status: int}
     */
    public function handle(string $slug): array
    {
        $offer = JobListing::query()
            ->with('organization')
            ->where('slug', $slug)
            ->first();

        if ($offer === null) {
            return ['offer' => null, 'status' => self::STATUS_NOT_FOUND];
        }

        if (! $this->isPublic($offer)) {
            return ['offer' => $offer, 'status' => self::STATUS_GONE];
        }

        return ['offer' => $offer, 'status' => self::STATUS_OK];
    }

    private function isPublic(JobListing $offer): bool
    {
        if ($offer->state !== JobListingState::ACTIVE) {
            return false;
        }

        if ($offer->application_deadline !== null
            && $offer->application_deadline->isPast()
            && ! $offer->application_deadline->isToday()
        ) {
            return false;
        }

        if ($offer->organization === null || ! $offer->organization->is_active) {
            return false;
        }

        return true;
    }
}
