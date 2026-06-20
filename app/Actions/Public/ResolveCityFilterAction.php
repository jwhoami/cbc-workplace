<?php

declare(strict_types=1);

namespace App\Actions\Public;

use App\Enums\JobListingState;
use App\Models\JobListing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Returns the dynamic list of cities to populate the public filter
 * dropdown, sourced from the distinct cities of currently active
 * offers (FR-010b).
 *
 * Per FR-010c the result MAY lag the underlying active-offer set by up
 * to 10 minutes to bound per-render cost; the dropdown's population
 * MUST NOT block first paint of the listing. We use a TTL cache here
 * (file-driver-compatible for cPanel hosting). JobListingObserver also
 * busts the cache on relevant state changes for best-effort freshness.
 */
class ResolveCityFilterAction
{
    use AsAction;

    public const CACHE_KEY = 'public.cities';

    public const CACHE_TTL_SECONDS = 600;

    /**
     * @return array<int, string>
     */
    public function handle(): array
    {
        return Cache::remember(
            self::CACHE_KEY,
            self::CACHE_TTL_SECONDS,
            fn (): array => $this->fetchDistinctCities(),
        );
    }

    /**
     * @return array<int, string>
     */
    private function fetchDistinctCities(): array
    {
        return JobListing::query()
            ->where('state', JobListingState::ACTIVE)
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->where(function (Builder $q): void {
                $q->whereNull('application_deadline')
                    ->orWhere('application_deadline', '>=', today());
            })
            ->whereHas('organization', function (Builder $q): void {
                $q->where('is_active', true);
            })
            ->distinct()
            ->orderBy('city')
            ->pluck('city')
            ->all();
    }
}
