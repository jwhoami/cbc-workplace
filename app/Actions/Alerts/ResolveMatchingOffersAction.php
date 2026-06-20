<?php

declare(strict_types=1);

namespace App\Actions\Alerts;

use App\Enums\JobListingState;
use App\Models\JobAlert;
use App\Models\JobListing;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class ResolveMatchingOffersAction
{
    use AsAction;

    public function handle(JobAlert $alert, Carbon $windowStart, Carbon $windowEnd): Collection
    {
        $query = JobListing::query()
            ->where('state', JobListingState::ACTIVE)
            ->where(function ($q) {
                $q->whereNull('application_deadline')
                    ->orWhere('application_deadline', '>=', now()->startOfDay());
            })
            ->whereHas('organization', fn ($q) => $q->where('is_active', true))
            ->where('published_at', '>=', $windowStart)
            ->where('published_at', '<=', $windowEnd);

        if ($alert->category_id !== null) {
            $query->whereHas('categories', fn ($q) => $q->where('categories.id', $alert->category_id));
        }

        if ($alert->city_folded !== null) {
            $query->where('job_listings.city_folded', $alert->city_folded);
        }

        return $query
            ->with([
                'organization:id,display_name',
                'categories:id,name',
            ])
            ->orderByDesc('published_at')
            ->get();
    }
}
