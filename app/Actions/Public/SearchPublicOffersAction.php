<?php

declare(strict_types=1);

namespace App\Actions\Public;

use App\Enums\JobListingState;
use App\Helpers\DiacriticFolder;
use App\Models\JobListing;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Resolves the public listing's offer set: active + non-expired + visible-org.
 *
 * Phase 3 (US1) wires the base scope and sort. Phase 4 (US2) extends with
 * the filters dictionary and accent-insensitive keyword search via the
 * title_folded/description_folded columns populated by JobListingObserver.
 *
 * Filter combination semantics (FR-010a):
 *   - Within a single filter type (e.g. work_mode = [remote, hybrid]):
 *     OR via whereIn.
 *   - Across filter types and the keyword: AND via chained where-clauses.
 *
 * Per FR-004/FR-005 the active set is: state = ACTIVE AND
 * (application_deadline IS NULL OR application_deadline >= today) AND
 * organization.is_active = true.
 */
class SearchPublicOffersAction
{
    use AsAction;

    /**
     * @param  array{
     *     category?: array<int, int>,
     *     work_mode?: array<int, int>,
     *     contract?: array<int, int>,
     *     city?: array<int, string>,
     * }  $filters
     */
    public function handle(
        ?string $keyword = null,
        array $filters = [],
        string $sort = 'recent',
        int $perPage = 20,
    ): LengthAwarePaginator {
        $query = $this->baseActiveQuery();

        $this->applyKeyword($query, $keyword);
        $this->applyFilters($query, $filters);
        $this->applySort($query, $sort);

        return $query
            ->with('organization:id,display_name,is_active')
            ->paginate($perPage)
            ->withQueryString();
    }

    private function baseActiveQuery(): Builder
    {
        return JobListing::query()
            ->where('state', JobListingState::ACTIVE)
            ->where(function (Builder $q): void {
                $q->whereNull('application_deadline')
                    ->orWhere('application_deadline', '>=', today());
            })
            ->whereHas('organization', function (Builder $q): void {
                $q->where('is_active', true);
            });
    }

    private function applyKeyword(Builder $query, ?string $keyword): void
    {
        if ($keyword === null || $keyword === '') {
            return;
        }

        $folded = DiacriticFolder::fold($keyword);
        if ($folded === '') {
            return;
        }

        $like = '%'.addcslashes($folded, '%_\\').'%';

        $query->where(function (Builder $q) use ($like): void {
            $q->where('title_folded', 'like', $like)
                ->orWhere('description_folded', 'like', $like);
        });
    }

    /**
     * @param  array{
     *     category?: array<int, int>,
     *     work_mode?: array<int, int>,
     *     contract?: array<int, int>,
     *     city?: array<int, string>,
     * }  $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        $categories = array_filter($filters['category'] ?? [], static fn ($v) => is_int($v) && $v > 0);
        if ($categories !== []) {
            $query->whereHas('categories', function (Builder $q) use ($categories): void {
                $q->whereIn('categories.id', array_values($categories));
            });
        }

        $workModes = array_filter($filters['work_mode'] ?? [], 'is_int');
        if ($workModes !== []) {
            $query->whereIn('work_modality', array_values($workModes));
        }

        $contractTypes = array_filter($filters['contract'] ?? [], 'is_int');
        if ($contractTypes !== []) {
            $query->whereIn('contract_type', array_values($contractTypes));
        }

        $cities = array_filter(
            $filters['city'] ?? [],
            static fn ($v) => is_string($v) && trim($v) !== ''
        );
        if ($cities !== []) {
            $query->whereIn('city', array_values($cities));
        }
    }

    private function applySort(Builder $query, string $sort): void
    {
        if ($sort === 'deadline') {
            // Per FR-007, offers without a deadline sort after offers that have one.
            $query
                ->orderByRaw('application_deadline IS NULL ASC')
                ->orderBy('application_deadline', 'asc')
                ->orderByDesc('id');

            return;
        }

        // Default: most-recent-first by publication date (FR-006).
        $query
            ->orderByDesc('published_at')
            ->orderByDesc('id');
    }
}
