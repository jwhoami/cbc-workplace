<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Actions\Public\RecordPublicEventAction;
use App\Actions\Public\ResolveCityFilterAction;
use App\Actions\Public\SearchPublicOffersAction;
use App\Enums\PublicEventKind;
use App\Enums\VisitorVariant;
use App\Helpers\DiacriticFolder;
use App\Http\Controllers\Controller;
use App\Http\Requests\Public\SearchOffersRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Public job board listing — anonymous-accessible, crawler-indexable
 * (FR-001, FR-022). Phase 3 (US1) introduced the basic browse-only
 * surface; Phase 4 (US2) adds keyword search, multi-select filters,
 * and observability events.
 */
class JobBoardController extends Controller
{
    public function __invoke(SearchOffersRequest $request): View
    {
        $input = $request->normalized();

        $offers = SearchPublicOffersAction::run(
            keyword: $input['keyword'],
            filters: $input['filters'],
            sort: $input['sort'],
            perPage: 20,
        );

        $cities = ResolveCityFilterAction::run();
        $jobCategories = Category::query()
            ->where('scope', 'JobListing')
            ->orderBy('name')
            ->get(['id', 'name']);

        $this->emitEvents($request, $input);

        return view('public.job-board', [
            'offers' => $offers,
            'cities' => $cities,
            'jobCategories' => $jobCategories,
            'currentSort' => $input['sort'],
            'activeFilters' => $input['filters'],
            'activeKeyword' => $input['keyword'],
        ]);
    }

    /**
     * @param  array{
     *     keyword: ?string,
     *     filters: array<string, array<int, int|string>>,
     *     sort: string,
     *     page: int,
     * }  $input
     */
    private function emitEvents(Request $request, array $input): void
    {
        $variant = VisitorVariant::Anonymous;

        RecordPublicEventAction::run(
            kind: PublicEventKind::PageView,
            request: $request,
            variant: $variant,
            pageNumber: $input['page'],
        );

        if ($input['keyword'] !== null) {
            RecordPublicEventAction::run(
                kind: PublicEventKind::KeywordQuery,
                request: $request,
                variant: $variant,
                pageNumber: $input['page'],
                payload: [
                    'folded_keyword' => DiacriticFolder::fold($input['keyword']),
                    'raw_length' => mb_strlen($input['keyword']),
                    'active_filters' => $input['filters'],
                ],
            );
        }

        $hasFilters = collect($input['filters'])->some(static fn ($values) => $values !== []);
        if ($hasFilters) {
            RecordPublicEventAction::run(
                kind: PublicEventKind::FilterChange,
                request: $request,
                variant: $variant,
                pageNumber: $input['page'],
                payload: [
                    'action' => 'apply',
                    'filters' => $input['filters'],
                ],
            );
        }
    }
}
