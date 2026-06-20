<?php

declare(strict_types=1);

namespace App\View\Components\Public;

use App\Models\JobListing;
use Illuminate\View\Component;
use Illuminate\View\View;

class OfferCard extends Component
{
    public function __construct(public JobListing $offer) {}

    public function render(): View
    {
        return view('components.public.offer-card');
    }

    public function detailUrl(): string
    {
        return url('/bolsa-de-trabajo/'.$this->offer->slug);
    }
}
