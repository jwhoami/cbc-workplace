<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>
        {{ $outcome === 'disabled'
            ? __('mail/job-alert.unsubscribe.confirmation_title')
            : __('mail/job-alert.unsubscribe.not_found_title') }}
        — {{ config('app.name') }}
    </title>
    @vite('resources/css/app.css')
    <style>
        body { font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; }
        .focus-ring:focus-visible { outline: 3px solid #2563eb; outline-offset: 2px; }
    </style>
</head>
<body class="bg-white text-gray-900">
    <main class="mx-auto max-w-xl p-6">
        <div role="status" aria-live="polite">
            @if ($outcome === 'disabled')
                <h1 class="text-2xl font-semibold mb-4 text-gray-900">
                    {{ __('mail/job-alert.unsubscribe.confirmation_title') }}
                </h1>
                <p class="text-base text-gray-800 mb-4">
                    {{ __('mail/job-alert.unsubscribe.confirmation_body') }}
                </p>

                @if ($alert)
                    <section aria-labelledby="alert-criteria-heading" class="mt-6">
                        <h2 id="alert-criteria-heading" class="text-lg font-medium text-gray-900 mb-2">
                            {{ __('mail/job-alert.unsubscribe.criteria_recap') }}
                        </h2>
                        <ul class="list-disc list-inside text-gray-800">
                            <li>
                                <strong>{{ __('models/job-alert.fields.category') }}:</strong>
                                {{ $alert->category?->name ?? __('models/job-alert.form.category_placeholder') }}
                            </li>
                            <li>
                                <strong>{{ __('models/job-alert.fields.city') }}:</strong>
                                {{ $alert->city ?? __('models/job-alert.form.city_placeholder') }}
                            </li>
                            <li>
                                <strong>{{ __('models/job-alert.fields.frequency') }}:</strong>
                                {{ $alert->frequency?->getLabel() }}
                            </li>
                        </ul>
                    </section>
                @endif
            @else
                <h1 class="text-2xl font-semibold mb-4 text-gray-900">
                    {{ __('mail/job-alert.unsubscribe.not_found_title') }}
                </h1>
                <p class="text-base text-gray-800 mb-4">
                    {{ __('mail/job-alert.unsubscribe.not_found_body') }}
                </p>
            @endif
        </div>

        <p class="mt-8">
            <a href="{{ url('/') }}"
               class="inline-block px-4 py-2 bg-blue-600 text-white rounded focus-ring hover:bg-blue-700">
                {{ __('mail/job-alert.unsubscribe.back_home') }}
            </a>
        </p>
    </main>
</body>
</html>
