{{--
    HTTP 404 Not Found — slug never existed in the system. Per FR-018 this
    is distinct from the 410 path: 404 means "try later", 410 means "gone
    forever". Crawlers use the distinction to manage their index.
--}}
<x-public.layout
    :title="__('public.not_found.title')"
    :description="__('public.not_found.message')"
    :noindex="true"
>
    <section
        class="bg-white border border-gray-200 rounded-lg p-8 text-center max-w-2xl mx-auto"
        role="status"
        aria-live="polite"
    >
        <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ __('public.not_found.title') }}</h1>
        <p class="text-gray-600 mb-6">{{ __('public.not_found.message') }}</p>

        <a
            href="{{ url('/bolsa-de-trabajo') }}"
            class="inline-block px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
        >
            {{ __('public.not_found.cta') }}
        </a>
    </section>
</x-public.layout>
