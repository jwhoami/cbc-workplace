<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} — Lazos de Fe</title>

    @if ($description)
        <meta name="description" content="{{ $description }}">
    @endif

    @if ($canonical)
        <link rel="canonical" href="{{ $canonical }}">
    @endif

    @if ($noindex)
        <meta name="robots" content="noindex,follow">
    @endif

    {{ $head ?? '' }}
    @stack('head')

    {{--
        Defensive Vite include — the public layout must render even when the
        Vite manifest is missing (e.g., a deploy where `npm run build` failed,
        or the exception handler trying to render a friendly error page on a
        broken instance). Without this guard, `@vite(...)` throws
        ViteManifestNotFoundException, which then bubbles back into the
        exception handler and re-renders the same broken layout — recursive
        500 with no body. Spec 007 FR-030 requires the friendly error page
        to remain renderable; a missing manifest must not block that.
    --}}
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        :focus-visible { outline: 3px solid #2563eb; outline-offset: 2px; }
        .skip-link {
            position: absolute; left: -9999px; top: auto; width: 1px; height: 1px; overflow: hidden;
        }
        .skip-link:focus {
            position: static; width: auto; height: auto;
            background: #1e40af; color: #ffffff; padding: 0.5rem 1rem; display: inline-block;
        }
    </style>
</head>
<body class="min-h-screen bg-gray-50 text-gray-900 antialiased">
    <a href="#main" class="skip-link">{{ __('Saltar al contenido principal') }}</a>

    <header class="bg-white border-b border-gray-200" role="banner">
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="{{ url('/') }}" class="text-lg font-semibold text-blue-700">Lazos de Fe</a>
            <nav aria-label="{{ __('Navegación principal') }}" class="text-sm flex gap-4 items-center">
                <a href="{{ url('/bolsa-de-trabajo') }}" class="text-gray-700 hover:text-blue-700">
                    {{ __('public.listing.title') }}
                </a>
                @auth('member')
                    <a href="{{ url('/member') }}" class="text-gray-700 hover:text-blue-700 font-semibold border-l border-gray-300 pl-4">
                        {{ __('Mi Cuenta') }}
                    </a>
                @endauth
            </nav>
        </div>
    </header>

    <main id="main" role="main" class="max-w-6xl mx-auto px-4 py-8">
        {{ $slot }}
    </main>

    <footer role="contentinfo" class="border-t border-gray-200 mt-12">
        <div class="max-w-6xl mx-auto px-4 py-6 text-sm text-gray-600">
            &copy; {{ date('Y') }} Lazos de Fe.
        </div>
    </footer>
</body>
</html>
