@use('App\Enums\VisitorVariant')

@if ($variant === VisitorVariant::Anonymous)
    <section
        class="mt-6 pt-6 border-t border-gray-200"
        aria-labelledby="apply-cta-heading"
        data-cta-variant="anonymous"
    >
        <h2 id="apply-cta-heading" class="text-lg font-semibold text-gray-900 mb-2">
            {{ __('public.cta.anonymous.title') }}
        </h2>
        <p class="text-gray-700 mb-3">{{ __('public.cta.anonymous.message') }}</p>
        <div class="flex flex-wrap gap-3">
            <a
                href="{{ $signInUrl() }}"
                class="inline-block px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
                aria-label="{{ __('public.cta.anonymous.sign_in') }} — {{ $offer->title }}"
            >
                {{ __('public.cta.anonymous.sign_in') }}
            </a>
            <a
                href="{{ $registerUrl() }}"
                class="inline-block px-4 py-2 border border-blue-600 text-blue-700 rounded-md hover:bg-blue-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
                aria-label="{{ __('public.cta.anonymous.register') }} — {{ $offer->title }}"
            >
                {{ __('public.cta.anonymous.register') }}
            </a>
        </div>
    </section>

@elseif ($variant === VisitorVariant::MemberWithoutCandidateProfile)
    <section
        class="mt-6 pt-6 border-t border-gray-200"
        aria-labelledby="apply-cta-heading"
        data-cta-variant="member_no_profile"
    >
        <h2 id="apply-cta-heading" class="text-lg font-semibold text-gray-900 mb-2">
            {{ __('public.cta.member_no_profile.title') }}
        </h2>
        <p class="text-gray-700 mb-3">{{ __('public.cta.member_no_profile.message') }}</p>
        <a
            href="{{ $completeProfileUrl() }}"
            class="inline-block px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
            aria-label="{{ __('public.cta.member_no_profile.complete_profile') }} — {{ $offer->title }}"
        >
            {{ __('public.cta.member_no_profile.complete_profile') }}
        </a>
    </section>

@elseif ($variant === VisitorVariant::MemberCandidate)
    <section
        class="mt-6 pt-6 border-t border-gray-200"
        aria-labelledby="apply-cta-heading"
        data-cta-variant="member_candidate"
    >
        <h2 id="apply-cta-heading" class="sr-only">{{ __('public.cta.member_candidate.button') }}</h2>
        <a
            href="{{ $applyUrl() }}"
            class="inline-block px-6 py-3 bg-blue-700 text-white text-lg font-semibold rounded-md hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
            aria-label="{{ __('public.cta.member_candidate.button') }} — {{ $offer->title }}"
        >
            {{ __('public.cta.member_candidate.button') }}
        </a>
    </section>
@elseif ($variant === VisitorVariant::Admin)
    <section
        class="mt-6 pt-6 border-t border-gray-200 bg-blue-50 border border-blue-200 rounded-md p-4"
        aria-labelledby="admin-info-heading"
    >
        <h2 id="admin-info-heading" class="text-sm font-semibold text-blue-800 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Vista previa de Administrador
        </h2>
        <p class="text-xs text-blue-700 mt-1">
            Estás visualizando esta oferta con un rol administrativo. El botón de postulación no se muestra para los administradores del sistema. Para probar el flujo de postulación, por favor inicia sesión con una cuenta de miembro candidato.
        </p>
    </section>
@endif

{{-- Admin variant intentionally renders nothing per Edge Case bullet 6. --}}
