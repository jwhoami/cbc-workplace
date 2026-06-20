<?php

declare(strict_types=1);

namespace App\View\Components\Public;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Base layout for every page on the public job-board surface (FR-032 WCAG 2.1 AA).
 * Renders the document shell, the skip link, the header, the main landmark, and the
 * footer. Title and description are passed in by each page.
 */
class Layout extends Component
{
    public function __construct(
        public string $title,
        public ?string $description = null,
        public ?string $canonical = null,
        public bool $noindex = false,
    ) {}

    public function render(): View
    {
        return view('components.public.layout');
    }
}
