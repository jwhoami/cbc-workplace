<?php

declare(strict_types=1);

namespace App\View\Components\Public;

use App\Enums\VisitorVariant;
use App\Models\JobListing;
use Illuminate\View\Component;
use Illuminate\View\View;

class ApplyCta extends Component
{
    public function __construct(
        public VisitorVariant $variant,
        public JobListing $offer,
    ) {}

    public function render(): View
    {
        return view('components.public.apply-cta');
    }

    public function detailUrl(): string
    {
        return url('/bolsa-de-trabajo/'.$this->offer->slug);
    }

    public function signInUrl(): string
    {
        return url('/member/login?redirect='.urlencode($this->detailUrl()));
    }

    public function registerUrl(): string
    {
        return url('/member/login');
    }

    public function completeProfileUrl(): string
    {
        return url('/member/candidate-profiles/create');
    }

    public function applyUrl(): string
    {
        return url('/member/apply/'.$this->offer->slug);
    }
}
