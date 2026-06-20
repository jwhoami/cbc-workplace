<?php

declare(strict_types=1);

namespace App\View\Components\Public;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * User-friendly error state for any backend failure on the public surface (FR-030).
 * Spec 007 explicitly forbids exposing stack traces or framework defaults to visitors.
 */
class ErrorState extends Component
{
    public function __construct(
        public ?string $title = null,
        public ?string $message = null,
        public ?string $retryUrl = null,
    ) {
        $this->title ??= __('public.error.title');
        $this->message ??= __('public.error.message');
    }

    public function render(): View
    {
        return view('components.public.error-state');
    }
}
