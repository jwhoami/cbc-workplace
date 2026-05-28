@use('App\Enums\VisitorVariant')

@if ($variant === VisitorVariant::Anonymous)
    <section
        class="mt-8 pt-8 border-t border-slate-800/60 bg-slate-950/20 border border-slate-900/80 rounded-2xl p-6 backdrop-blur-sm shadow-sm"
        aria-labelledby="apply-cta-heading"
        data-cta-variant="anonymous"
    >
        <h2 id="apply-cta-heading" class="text-lg font-bold text-slate-200 mb-2">
            {{ __('public.cta.anonymous.title') }}
        </h2>
        <p class="text-slate-400 text-sm mb-4 leading-relaxed">{{ __('public.cta.anonymous.message') }}</p>
        <div class="flex flex-wrap gap-3">
            <a
                href="{{ $signInUrl() }}"
                class="inline-block px-5 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white font-medium rounded-xl hover:-translate-y-0.5 transition-all duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-cyan-500 shadow-lg active:scale-95 text-sm"
                aria-label="{{ __('public.cta.anonymous.sign_in') }} — {{ $offer->title }}"
            >
                {{ __('public.cta.anonymous.sign_in') }}
            </a>
            <a
                href="{{ $registerUrl() }}"
                class="inline-block px-5 py-2.5 border border-slate-700 bg-slate-900/40 text-slate-300 font-medium rounded-xl hover:bg-slate-800/50 hover:text-white transition-all duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-cyan-500 active:scale-95 text-sm"
                aria-label="{{ __('public.cta.anonymous.register') }} — {{ $offer->title }}"
            >
                {{ __('public.cta.anonymous.register') }}
            </a>
        </div>
    </section>

@elseif ($variant === VisitorVariant::MemberWithoutCandidateProfile)
    <section
        class="mt-8 pt-8 border-t border-slate-800/60 bg-slate-950/20 border border-slate-900/80 rounded-2xl p-6 backdrop-blur-sm shadow-sm"
        aria-labelledby="apply-cta-heading"
        data-cta-variant="member_no_profile"
    >
        <h2 id="apply-cta-heading" class="text-lg font-bold text-slate-200 mb-2">
            {{ __('public.cta.member_no_profile.title') }}
        </h2>
        <p class="text-slate-400 text-sm mb-4 leading-relaxed">{{ __('public.cta.member_no_profile.message') }}</p>
        <a
            href="{{ $completeProfileUrl() }}"
            class="inline-block px-5 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white font-medium rounded-xl hover:-translate-y-0.5 transition-all duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-cyan-500 shadow-lg active:scale-95 text-sm"
            aria-label="{{ __('public.cta.member_no_profile.complete_profile') }} — {{ $offer->title }}"
        >
            {{ __('public.cta.member_no_profile.complete_profile') }}
        </a>
    </section>

@elseif ($variant === VisitorVariant::MemberCandidate)
    <section
        class="mt-8 pt-8 border-t border-slate-800/60"
        aria-labelledby="apply-cta-heading"
        data-cta-variant="member_candidate"
    >
        <h2 id="apply-cta-heading" class="sr-only">{{ __('public.cta.member_candidate.button') }}</h2>
        <a
            href="{{ $applyUrl() }}"
            class="inline-block px-8 py-3.5 bg-gradient-to-r from-cyan-600 to-teal-600 hover:from-cyan-500 hover:to-teal-500 text-white font-bold rounded-xl hover:-translate-y-0.5 transition-all duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-cyan-500 shadow-lg active:scale-95 text-lg w-full sm:w-auto text-center"
            aria-label="{{ __('public.cta.member_candidate.button') }} — {{ $offer->title }}"
        >
            {{ __('public.cta.member_candidate.button') }}
        </a>
    </section>
@elseif ($variant === VisitorVariant::Admin)
    <section
        class="mt-8 pt-6 border-t border-slate-800/60 bg-cyan-950/20 border border-cyan-900/50 rounded-2xl p-5 shadow-sm backdrop-blur-sm"
        aria-labelledby="admin-info-heading"
    >
        <h2 id="admin-info-heading" class="text-sm font-bold text-cyan-300 flex items-center gap-2">
            <svg class="w-5 h-5 text-cyan-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Vista previa de Administrador
        </h2>
        <p class="text-xs text-cyan-400 mt-1.5 leading-relaxed">
            Estás visualizando esta oferta con un rol administrativo. El botón de postulación no se muestra para los administradores del sistema. Para probar el flujo de postulación, por favor inicia sesión con una cuenta de miembro candidato.
        </p>
    </section>
@endif

{{-- Admin variant intentionally renders nothing per Edge Case bullet 6. --}}
