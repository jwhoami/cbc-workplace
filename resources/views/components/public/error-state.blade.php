<section
    class="bg-red-50 border border-red-200 rounded-lg p-8 text-center"
    role="alert"
    aria-live="assertive"
>
    <h2 class="text-xl font-semibold text-red-900 mb-2">{{ $title }}</h2>
    <p class="text-red-800 mb-4">{{ $message }}</p>

    @if ($retryUrl)
        <a
            href="{{ $retryUrl }}"
            class="inline-block px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2"
        >
            {{ __('public.error.retry') }}
        </a>
    @endif
</section>
