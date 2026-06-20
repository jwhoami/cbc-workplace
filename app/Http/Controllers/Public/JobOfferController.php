<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Actions\Public\RecordPublicEventAction;
use App\Actions\Public\ResolveOfferBySlugAction;
use App\Enums\PublicEventKind;
use App\Helpers\VisitorVariantResolver;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Public offer detail page (FR-002, FR-014, FR-018, FR-019).
 *
 * Per FR-018, the controller distinguishes:
 *   - 200 OK  → active offer, full detail rendered with variant CTA
 *   - 410 Gone → slug previously existed but offer is no longer active
 *   - 404 Not Found → slug never existed
 *
 * The 410 path emits a friendly Spanish "no longer available" view
 * (still rendered with the public layout for crawler consistency); the
 * 404 path emits the public 404 view.
 */
class JobOfferController extends Controller
{
    public function show(Request $request, string $slug): Response
    {
        $resolution = ResolveOfferBySlugAction::run($slug);

        if ($resolution['status'] === ResolveOfferBySlugAction::STATUS_NOT_FOUND) {
            return response()->view('public.errors.not-found', [], ResponseAlias::HTTP_NOT_FOUND);
        }

        if ($resolution['status'] === ResolveOfferBySlugAction::STATUS_GONE) {
            return response()->view('public.errors.gone', [
                'offer' => $resolution['offer'],
            ], ResponseAlias::HTTP_GONE)
                ->header('Cache-Control', 'public, max-age=86400');
        }

        $offer = $resolution['offer'];
        $variant = VisitorVariantResolver::resolve();

        RecordPublicEventAction::run(
            kind: PublicEventKind::DetailOpen,
            request: $request,
            variant: $variant,
            payload: [
                'slug' => $offer->slug,
                'offer_id' => $offer->id,
            ],
        );

        return response()
            ->view('public.job-offer', [
                'offer' => $offer,
                'variant' => $variant,
            ])
            ->header('Cache-Control', 'public, max-age=300, stale-while-revalidate=3600');
    }
}
