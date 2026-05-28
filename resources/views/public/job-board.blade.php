@php
    use App\Enums\ContractType;
    use App\Enums\WorkModality;

    // Per FR-024 + FR-027 the unfiltered, page-1 listing is the only
    // canonically-indexable variant. Any active filter or page>1 sets
    // `noindex,follow` so duplicate-content variants don't pollute the index.
    $hasFilters = collect($activeFilters)->some(fn ($values) => $values !== [])
        || ($activeKeyword !== null);
    $isPaginatedDeep = request()->integer('page', 1) > 1;
    $shouldNoindex = $hasFilters || $isPaginatedDeep;

    $countActiveFilters = collect($activeFilters)->sum(fn ($values) => count($values))
        + ($activeKeyword !== null ? 1 : 0);
@endphp

<x-public.layout
    :title="__('public.listing.title')"
    :description="__('public.listing.subtitle')"
    :canonical="url('/bolsa-de-trabajo')"
    :noindex="$shouldNoindex"
>
    <header class="mb-10">
        <h1 class="text-4xl font-extrabold tracking-tight bg-gradient-to-r from-white via-slate-100 to-slate-400 bg-clip-text text-transparent sm:text-5xl">
            {{ __('public.listing.title') }}
        </h1>
        <p class="text-slate-400 mt-2 text-lg max-w-2xl font-light">
            {{ __('public.listing.subtitle') }}
        </p>
    </header>

    <form
        method="GET"
        action="{{ url('/bolsa-de-trabajo') }}"
        class="mb-8"
        id="public-search-form"
        role="search"
        aria-label="{{ __('public.filters.title') }}"
    >
        <div class="flex flex-col md:flex-row gap-4 mb-5">
            <label class="flex-1 block">
                <span class="sr-only">{{ __('public.filters.search_placeholder') }}</span>
                <input
                    type="search"
                    name="q"
                    id="public-search-input"
                    value="{{ $activeKeyword ?? '' }}"
                    placeholder="{{ __('public.filters.search_placeholder') }}"
                    autocomplete="off"
                    inputmode="search"
                    maxlength="200"
                    class="w-full px-4 py-3 bg-slate-900/60 border border-slate-800 text-slate-100 rounded-xl placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-300 backdrop-blur-sm shadow-sm"
                >
            </label>

            <label class="block md:w-56">
                <span class="sr-only">{{ __('public.filters.sort.label') }}</span>
                <select
                    name="sort"
                    onchange="document.getElementById('public-search-form').submit()"
                    class="w-full px-4 py-3 bg-slate-900/60 border border-slate-800 text-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-300 backdrop-blur-sm cursor-pointer shadow-sm"
                >
                    <option value="recent" @selected($currentSort === 'recent')>{{ __('public.filters.sort.recent') }}</option>
                    <option value="deadline" @selected($currentSort === 'deadline')>{{ __('public.filters.sort.deadline') }}</option>
                </select>
            </label>
        </div>

        <details class="mb-6 border border-slate-800/80 rounded-xl bg-slate-900/40 backdrop-blur-sm overflow-hidden transition-all duration-300 shadow-sm" @if ($countActiveFilters > 0) open @endif>
            <summary class="cursor-pointer px-5 py-4 font-semibold text-slate-200 select-none hover:bg-slate-800/20 transition-colors flex items-center justify-between outline-none">
                <span class="flex items-center gap-2">
                    {{ __('public.filters.title') }}
                    @if ($countActiveFilters > 0)
                        <span class="px-2 py-0.5 text-xs font-semibold bg-indigo-500/20 text-indigo-300 rounded-full">
                            {{ $countActiveFilters }}
                        </span>
                    @endif
                </span>
                <span class="text-slate-500 transition-transform duration-300 group-open:rotate-180">▼</span>
            </summary>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 px-5 py-5 border-t border-slate-800/60">
                {{-- Category --}}
                @if ($jobCategories->isNotEmpty())
                    <fieldset>
                        <legend class="font-semibold text-slate-200 mb-3 text-sm tracking-wide uppercase">{{ __('public.filters.category') }}</legend>
                        @foreach ($jobCategories as $cat)
                            <label class="flex items-center gap-3 mb-2 cursor-pointer group">
                                <input
                                    type="checkbox"
                                    name="category[]"
                                    value="{{ $cat->id }}"
                                    @checked(in_array($cat->id, $activeFilters['category'] ?? [], true))
                                    onchange="document.getElementById('public-search-form').submit()"
                                    class="rounded border-slate-700 bg-slate-950 text-indigo-600 focus:ring-indigo-500 transition-all duration-200 cursor-pointer"
                                >
                                <span class="text-sm text-slate-300 group-hover:text-indigo-400 transition-colors">{{ $cat->name }}</span>
                            </label>
                        @endforeach
                    </fieldset>
                @endif

                {{-- Work Modality --}}
                <fieldset>
                    <legend class="font-semibold text-slate-200 mb-3 text-sm tracking-wide uppercase">{{ __('public.filters.work_mode') }}</legend>
                    @foreach (WorkModality::cases() as $mode)
                        <label class="flex items-center gap-3 mb-2 cursor-pointer group">
                            <input
                                type="checkbox"
                                name="work_mode[]"
                                value="{{ $mode->value }}"
                                @checked(in_array($mode->value, $activeFilters['work_mode'] ?? [], true))
                                onchange="document.getElementById('public-search-form').submit()"
                                class="rounded border-slate-700 bg-slate-950 text-indigo-600 focus:ring-indigo-500 transition-all duration-200 cursor-pointer"
                            >
                            <span class="text-sm text-slate-300 group-hover:text-indigo-400 transition-colors">{{ $mode->getLabel() }}</span>
                        </label>
                    @endforeach
                </fieldset>

                {{-- Contract Type --}}
                <fieldset>
                    <legend class="font-semibold text-slate-200 mb-3 text-sm tracking-wide uppercase">{{ __('public.filters.contract') }}</legend>
                    @foreach (ContractType::cases() as $type)
                        <label class="flex items-center gap-3 mb-2 cursor-pointer group">
                            <input
                                type="checkbox"
                                name="contract[]"
                                value="{{ $type->value }}"
                                @checked(in_array($type->value, $activeFilters['contract'] ?? [], true))
                                onchange="document.getElementById('public-search-form').submit()"
                                class="rounded border-slate-700 bg-slate-950 text-indigo-600 focus:ring-indigo-500 transition-all duration-200 cursor-pointer"
                            >
                            <span class="text-sm text-slate-300 group-hover:text-indigo-400 transition-colors">{{ $type->getLabel() }}</span>
                        </label>
                    @endforeach
                </fieldset>

                {{-- City (dynamic per FR-010b) --}}
                @if (! empty($cities))
                    <fieldset>
                        <legend class="font-semibold text-slate-200 mb-3 text-sm tracking-wide uppercase">{{ __('public.filters.city') }}</legend>
                        <div class="max-h-40 overflow-y-auto pr-2 custom-scrollbar">
                            @foreach ($cities as $city)
                                <label class="flex items-center gap-3 mb-2 cursor-pointer group">
                                    <input
                                        type="checkbox"
                                        name="city[]"
                                        value="{{ $city }}"
                                        @checked(in_array($city, $activeFilters['city'] ?? [], true))
                                        onchange="document.getElementById('public-search-form').submit()"
                                        class="rounded border-slate-700 bg-slate-950 text-indigo-600 focus:ring-indigo-500 transition-all duration-200 cursor-pointer"
                                    >
                                    <span class="text-sm text-slate-300 group-hover:text-indigo-400 transition-colors">{{ $city }}</span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                @endif
            </div>

            @if ($countActiveFilters > 0)
                <div class="px-5 py-4 border-t border-slate-800/60 bg-slate-950/40 rounded-b-xl flex items-center justify-between">
                    <a
                        href="{{ url('/bolsa-de-trabajo') }}"
                        class="text-sm font-medium text-indigo-400 hover:text-indigo-300 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 rounded px-2 py-1 bg-indigo-500/10 hover:bg-indigo-500/20"
                    >
                        ✕ {{ __('public.filters.clear_all') }}
                    </a>
                </div>
            @endif
        </details>

        <noscript>
            <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl transition-colors shadow-lg">
                {{ __('public.filters.apply') }}
            </button>
        </noscript>
    </form>

    <div
        class="text-sm text-slate-400 mb-5 bg-slate-900/30 border border-slate-800/50 rounded-lg px-4 py-2 inline-flex items-center gap-2 backdrop-blur-sm shadow-sm"
        role="status"
        aria-live="polite"
        id="result-count"
    >
        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
        {{ trans_choice('public.listing.result_count', $offers->total(), ['count' => $offers->total()]) }}
    </div>

    @if ($offers->isEmpty())
        @if ($countActiveFilters > 0)
            <x-public.empty-state
                :title="__('public.listing.empty.with_filters.title')"
                :message="__('public.listing.empty.with_filters.message')"
                :ctaLabel="__('public.listing.empty.with_filters.cta')"
                :ctaUrl="url('/bolsa-de-trabajo')"
            />
        @else
            <x-public.empty-state
                :title="__('public.listing.empty.title')"
                :message="__('public.listing.empty.message')"
            />
        @endif
    @else
        <ul class="space-y-4 mb-8 list-none p-0" role="list">
            @foreach ($offers as $offer)
                <li>
                    <x-public.offer-card :offer="$offer" />
                </li>
            @endforeach
        </ul>

        @if ($offers->hasPages())
            <x-public.pagination-nav :paginator="$offers" />
        @endif
    @endif

    @push('head')
        <script>
            // Debounced live search per FR-009a — submits the form 300 ms
            // after the visitor's last keystroke. Vanilla JS to keep the
            // public surface dependency-free.
            (function () {
                const input = document.getElementById('public-search-input');
                const form = document.getElementById('public-search-form');
                if (!input || !form) return;

                let timer = null;
                let lastValue = input.value;

                input.addEventListener('input', function () {
                    if (input.value === lastValue) return;
                    lastValue = input.value;

                    if (timer) clearTimeout(timer);
                    timer = setTimeout(function () {
                        // Drop any current page param so debounced search
                        // always starts on page 1.
                        const pageInput = form.querySelector('input[name="page"]');
                        if (pageInput) pageInput.remove();
                        form.submit();
                    }, 300);
                });
            })();
        </script>
    @endpush
</x-public.layout>
