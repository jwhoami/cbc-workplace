<section
    class="bg-white border border-gray-200 rounded-lg p-8 text-center"
    role="status"
    aria-live="polite"
>
    <h2 class="text-xl font-semibold text-gray-900 mb-2">{{ $title }}</h2>
    <p class="text-gray-600 mb-4">{{ $message }}</p>

    @if ($ctaLabel && $ctaUrl)
        <a
            href="{{ $ctaUrl }}"
            class="inline-block px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
        >
            {{ $ctaLabel }}
        </a>
    @endif
</section>
