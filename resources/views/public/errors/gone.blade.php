{{--
    HTTP 410 Gone — slug previously existed but offer is no longer active
    (expired, unpublished, or organization hidden). Per FR-018 this page
    must remain crawler-readable so search engines can deindex cleanly.
--}}
<x-public.layout
    :title="__('public.gone.title')"
    :description="__('public.gone.message')"
    :noindex="true"
>
    <section
        class="bg-white border border-gray-200 rounded-lg p-8 text-center max-w-2xl mx-auto"
        role="status"
        aria-live="polite"
    >
        <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ __('public.gone.title') }}</h1>

        @if (isset($offer) && $offer)
            <p class="text-gray-600 mb-1">
                <span class="font-medium">{{ $offer->title }}</span>
            </p>
        @endif

        <p class="text-gray-600 mb-6">{{ __('public.gone.message') }}</p>

        <a
            href="{{ url('/bolsa-de-trabajo') }}"
            class="inline-block px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
        >
            {{ __('public.gone.cta') }}
        </a>
    </section>
</x-public.layout>
