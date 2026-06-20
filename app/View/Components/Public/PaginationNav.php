<?php

declare(strict_types=1);

namespace App\View\Components\Public;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Numbered pagination with prev/next per FR-020a. Each page must have a
 * stable, server-rendered URL with all current query parameters preserved
 * (FR-021).
 */
class PaginationNav extends Component
{
    public function __construct(public LengthAwarePaginator $paginator) {}

    public function render(): View
    {
        return view('components.public.pagination-nav');
    }
}
