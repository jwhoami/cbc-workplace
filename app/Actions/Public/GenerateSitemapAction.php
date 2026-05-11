<?php

declare(strict_types=1);

namespace App\Actions\Public;

use App\Enums\JobListingState;
use App\Models\JobListing;
use Illuminate\Database\Eloquent\Builder;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\AsJob;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

/**
 * Builds an XML sitemap covering every crawler-indexable surface of the
 * public job board (FR-023):
 *  - The listing root URL (`/bolsa-de-trabajo`).
 *  - One URL per active, non-expired, visible-org JobListing using its
 *    canonical detail slug. `lastmod` is the listing's `updated_at`.
 *
 * The active-set predicate mirrors `SearchPublicOffersAction::baseActiveQuery()`
 * so the sitemap and the live listing never diverge.
 *
 * Returns the absolute path of the file the sitemap was written to so callers
 * (the artisan command, the on-demand controller backstop) can report it.
 */
class GenerateSitemapAction
{
    use AsAction;
    use AsJob;

    /**
     * @return array{path: string, count: int} the absolute file path and the
     *                                         number of `<url>` entries written (listing root + each active offer)
     */
    public function handle(?string $path = null): array
    {
        $path = $path ?? public_path('sitemap.xml');

        $sitemap = Sitemap::create()
            ->add(
                Url::create(url('/bolsa-de-trabajo'))
                    ->setLastModificationDate(now())
            );

        $count = 1;
        $this->activeOffers()->each(function (JobListing $offer) use ($sitemap, &$count): void {
            $sitemap->add(
                Url::create(url('/bolsa-de-trabajo/'.$offer->slug))
                    ->setLastModificationDate($offer->updated_at ?? now())
            );
            $count++;
        });

        // Atomic write: build into a sibling tmp file then rename(2). Two
        // concurrent regenerations (e.g. a crawler hitting the on-demand
        // backstop while the scheduler runs) can race on the canonical
        // path; rename is atomic on POSIX, so readers always see either
        // the previous file or the fully-written new one — never a
        // partial XML payload.
        $tmpPath = $path.'.'.bin2hex(random_bytes(4)).'.tmp';
        $sitemap->writeToFile($tmpPath);
        rename($tmpPath, $path);

        return ['path' => $path, 'count' => $count];
    }

    private function activeOffers(): Builder
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
}
