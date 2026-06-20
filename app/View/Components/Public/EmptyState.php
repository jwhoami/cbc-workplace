<?php

declare(strict_types=1);

namespace App\View\Components\Public;

use Illuminate\View\Component;
use Illuminate\View\View;

class EmptyState extends Component
{
    public function __construct(
        public string $title,
        public string $message,
        public ?string $ctaLabel = null,
        public ?string $ctaUrl = null,
    ) {}

    public function render(): View
    {
        return view('components.public.empty-state');
    }
}
