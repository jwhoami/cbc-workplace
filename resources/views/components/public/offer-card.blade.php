<article class="bg-slate-900/40 border border-slate-800/80 rounded-2xl p-6 hover:border-cyan-500/80 hover:bg-slate-900/60 hover:-translate-y-1 transition-all duration-300 shadow-sm hover:shadow-cyan-500/5 hover:shadow-lg backdrop-blur-sm group">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold mb-1.5">
                <a
                    href="{{ $detailUrl() }}"
                    class="text-slate-100 hover:text-cyan-400 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-cyan-500 rounded"
                >
                    {{ $offer->title }}
                </a>
            </h2>

            @if ($offer->organization)
                <p class="text-slate-400 text-sm font-medium mb-4 md:mb-0">
                    <span class="sr-only">{{ __('public.listing.row.organization') }}:</span>
                    {{ $offer->organization->display_name }}
                </p>
            @endif
        </div>

        <div class="flex flex-wrap gap-2 items-center md:justify-end">
            @if ($offer->city)
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-lg text-xs font-medium bg-slate-800/80 text-slate-300 border border-slate-700/50 shadow-sm">
                    <span class="sr-only">{{ __('public.listing.row.city') }}:</span>
                    📍 {{ $offer->city }}
                </span>
            @endif

            @if ($offer->work_modality)
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-lg text-xs font-medium bg-cyan-500/10 text-cyan-300 border border-cyan-500/20 shadow-sm">
                    <span class="sr-only">{{ __('public.listing.row.work_mode') }}:</span>
                    💼 {{ $offer->work_modality->getLabel() }}
                </span>
            @endif

            @if ($offer->contract_type)
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-lg text-xs font-medium bg-amber-500/10 text-amber-300 border border-amber-500/20 shadow-sm">
                    <span class="sr-only">{{ __('public.listing.row.contract_type') }}:</span>
                    📄 {{ $offer->contract_type->getLabel() }}
                </span>
            @endif
        </div>
    </div>

    @if ($offer->published_at)
        <div class="mt-4 pt-4 border-t border-slate-800/60 flex justify-between items-center text-xs text-slate-500">
            <time datetime="{{ $offer->published_at->toIso8601String() }}">
                {{ __('public.listing.row.published_on', ['date' => $offer->published_at->isoFormat('LL')]) }}
            </time>
            
            <a href="{{ $detailUrl() }}" class="text-cyan-400 hover:text-cyan-300 font-medium inline-flex items-center gap-1 opacity-0 group-hover:opacity-100 group-focus-within:opacity-100 transition-opacity duration-300">
                Ver detalle <span class="transition-transform group-hover:translate-x-1 duration-200">→</span>
            </a>
        </div>
    @endif
</article>
