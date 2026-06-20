@php
    $hasSalary = $offer->salary_min !== null || $offer->salary_max !== null;
    $detailUrl = url('/bolsa-de-trabajo/'.$offer->slug);
@endphp

<x-public.layout
    :title="$offer->title"
    :description="\Illuminate\Support\Str::limit(strip_tags((string) $offer->description), 155)"
    :canonical="$detailUrl"
>
    @push('head')
        @include('public.partials.json-ld', ['offer' => $offer])
        @include('public.partials.og-tags', ['offer' => $offer, 'detailUrl' => $detailUrl])
    @endpush

    <article class="bg-slate-900/40 border border-slate-800/80 rounded-2xl p-8 backdrop-blur-sm shadow-sm">
        <header class="mb-6 pb-6 border-b border-slate-800/60">
            <p class="text-sm text-slate-400 mb-3">
                <a
                    href="{{ url('/bolsa-de-trabajo') }}"
                    class="text-cyan-400 hover:text-cyan-300 font-medium inline-flex items-center gap-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-cyan-500 rounded transition-colors"
                >
                    ← {{ __('public.listing.title') }}
                </a>
            </p>
            <h1 class="text-3xl font-extrabold tracking-tight text-slate-100 sm:text-4xl">{{ $offer->title }}</h1>
            @if ($offer->organization)
                <p class="text-lg text-cyan-300 font-medium mt-2">{{ $offer->organization->display_name }}</p>
            @endif
        </header>

        <div class="flex flex-wrap gap-2.5 mb-6 items-center">
            @if ($offer->city)
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-lg text-xs font-semibold bg-slate-800/80 text-slate-300 border border-slate-700/50 shadow-sm">
                    <span class="sr-only">{{ __('public.detail.location') }}:</span>
                    📍 {{ $offer->city }}{{ $offer->province ? ', '.$offer->province : '' }}
                </span>
            @endif

            @if ($offer->work_modality)
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-lg text-xs font-semibold bg-cyan-500/10 text-cyan-300 border border-cyan-500/20 shadow-sm">
                    <span class="sr-only">{{ __('public.detail.work_mode') }}:</span>
                    💼 {{ $offer->work_modality->getLabel() }}
                </span>
            @endif

            @if ($offer->contract_type)
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-lg text-xs font-semibold bg-amber-500/10 text-amber-300 border border-amber-500/20 shadow-sm">
                    <span class="sr-only">{{ __('public.detail.contract_type') }}:</span>
                    📄 {{ $offer->contract_type->getLabel() }}
                </span>
            @endif

            @if ($offer->categories->isNotEmpty())
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-lg text-xs font-semibold bg-slate-800/60 text-slate-400 border border-slate-700/30 shadow-sm">
                    🏷️ {{ $offer->categories->pluck('name')->join(', ') }}
                </span>
            @endif

            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-lg text-xs font-semibold bg-slate-800/60 text-slate-400 border border-slate-700/30 shadow-sm">
                💰 
                @if ($hasSalary)
                    {{ $offer->currency }}
                    {{ $offer->salary_min ? number_format((float) $offer->salary_min, 2) : '—' }}
                    @if ($offer->salary_max)
                        – {{ number_format((float) $offer->salary_max, 2) }}
                    @endif
                @else
                    <span class="italic text-slate-500">{{ __('public.detail.salary_unspecified') }}</span>
                @endif
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8 bg-slate-950/40 border border-slate-900 rounded-xl p-4 text-sm text-slate-400">
            @if ($offer->published_at)
                <div class="flex items-center gap-2">
                    <span class="text-slate-500">📅 {{ __('public.detail.publication_date') }}:</span>
                    <time datetime="{{ $offer->published_at->toIso8601String() }}" class="font-medium text-slate-300">
                        {{ $offer->published_at->isoFormat('LL') }}
                    </time>
                </div>
            @endif

            @if ($offer->application_deadline)
                <div class="flex items-center gap-2">
                    <span class="text-slate-500">⏳ {{ __('public.detail.application_deadline') }}:</span>
                    <time datetime="{{ $offer->application_deadline->toDateString() }}" class="font-medium text-slate-300">
                        {{ $offer->application_deadline->isoFormat('LL') }}
                    </time>
                </div>
            @endif
        </div>

        @if ($offer->description)
            <section class="mb-8">
                <h2 class="text-xl font-bold text-slate-200 mb-3 tracking-tight">{{ __('public.detail.description') }}</h2>
                <div class="prose prose-invert prose-sm max-w-none text-slate-300 whitespace-pre-wrap leading-relaxed">{{ $offer->description }}</div>
            </section>
        @endif

        @if ($offer->requirements)
            <section class="mb-8">
                <h2 class="text-xl font-bold text-slate-200 mb-3 tracking-tight">{{ __('public.detail.requirements') }}</h2>
                <div class="prose prose-invert prose-sm max-w-none text-slate-300 whitespace-pre-wrap leading-relaxed">{{ $offer->requirements }}</div>
            </section>
        @endif

        @if ($offer->organization)
            <section class="mb-8 pt-6 border-t border-slate-800/60">
                <h2 class="text-xl font-bold text-slate-200 mb-4 tracking-tight">{{ __('public.detail.organization') }}</h2>
                <div class="flex flex-col sm:flex-row gap-4 items-start bg-slate-950/20 border border-slate-900 p-5 rounded-2xl backdrop-blur-sm">
                    @if ($offer->organization->logo)
                        <img
                            src="{{ \Illuminate\Support\Facades\Storage::url($offer->organization->logo) }}"
                            alt="{{ $offer->organization->display_name }}"
                            class="w-16 h-16 object-contain rounded-xl border border-slate-800 bg-slate-900/60 p-2 shrink-0 shadow-sm"
                            loading="lazy"
                        >
                    @endif
                    <div class="flex-1">
                        <h3 class="font-bold text-slate-200">{{ $offer->organization->display_name }}</h3>
                        @if ($offer->organization->description)
                            <p class="text-slate-400 text-sm mt-1.5 leading-relaxed">{{ $offer->organization->description }}</p>
                        @endif
                        @if ($offer->organization->website)
                            <p class="mt-3">
                                <a
                                    href="{{ $offer->organization->website }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-cyan-400 hover:text-cyan-300 font-medium inline-flex items-center gap-1 text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-cyan-500 rounded transition-colors"
                                >
                                    {{ __('public.detail.organization_website') }} <span class="text-xs">↗</span>
                                </a>
                            </p>
                        @endif
                    </div>
                </div>
            </section>
        @endif

        {{-- Apply CTA — variant-aware per FR-019. --}}
        <x-public.apply-cta :variant="$variant" :offer="$offer" />
    </article>
</x-public.layout>
