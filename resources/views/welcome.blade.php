<x-public.layout
    title="Conectando Propósito y Talento"
    description="Lazos de Fe es la plataforma de empleo y colaboración que une a profesionales comprometidos con organizaciones y emprendimientos alineados con valores y principios éticos compartidos."
>
    {{-- Hero Section --}}
    <section class="relative overflow-hidden py-16 md:py-24 text-center md:text-left">
        <div class="absolute inset-0 bg-gradient-to-tr from-cyan-500/10 via-teal-500/5 to-transparent blur-3xl pointer-events-none"></div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-cyan-500/10 text-cyan-300 border border-cyan-500/20 shadow-sm">
                    ✨ Plataforma Oficial de Empleo y Emprendimiento
                </div>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-white leading-tight">
                    Conectando el <span class="bg-gradient-to-r from-cyan-400 via-teal-400 to-amber-400 bg-clip-text text-transparent">talento</span> con el propósito eterno
                </h1>
                <p class="text-slate-400 text-lg max-w-2xl font-light leading-relaxed">
                    Lazos de Fe une a profesionales comprometidos con organizaciones, ministerios y emprendimientos que buscan expandir su obra. Descubre oportunidades de empleo y servicios alineados con tus valores de fe.
                </p>
                <div class="pt-4 flex flex-wrap gap-4 justify-center md:justify-start">
                    <a
                        href="{{ url('/bolsa-de-trabajo') }}"
                        class="inline-flex items-center justify-center px-6 py-3.5 bg-gradient-to-r from-cyan-600 to-teal-600 hover:from-cyan-500 hover:to-teal-500 text-white font-bold rounded-xl hover:-translate-y-0.5 transition-all duration-300 shadow-lg hover:shadow-cyan-500/25 active:scale-95 text-sm"
                    >
                        Explorar Bolsa de Trabajo <span class="ml-2">→</span>
                    </a>
                    <a
                        href="{{ url('/app') }}"
                        class="inline-flex items-center justify-center px-6 py-3.5 border border-amber-500/30 bg-amber-500/5 text-amber-300 font-bold rounded-xl hover:bg-amber-500/10 hover:text-amber-200 transition-all duration-300 active:scale-95 shadow-sm text-sm"
                    >
                        Directorio de Emprendimientos
                    </a>
                    <a
                        href="{{ url('/member') }}"
                        class="inline-flex items-center justify-center px-6 py-3.5 border border-slate-700 bg-slate-900/40 text-slate-300 font-bold rounded-xl hover:bg-slate-800/50 hover:text-white transition-all duration-300 active:scale-95 shadow-sm text-sm"
                    >
                        Acceso Miembros
                    </a>
                </div>
            </div>

            <div class="lg:col-span-5 relative">
                <div class="absolute -inset-1 bg-gradient-to-r from-cyan-500 to-teal-600 rounded-2xl blur opacity-25 group-hover:opacity-100 transition duration-1000 group-hover:duration-200"></div>
                <div class="relative bg-slate-900/60 border border-slate-800/80 p-8 rounded-2xl backdrop-blur-md shadow-2xl">
                    <div class="flex items-center justify-between pb-6 border-b border-slate-800/60 mb-6">
                        <span class="font-bold text-slate-200 text-lg">Resumen de Actividad</span>
                        <span class="w-3.5 h-3.5 rounded-full bg-emerald-500 animate-ping"></span>
                    </div>
                    <div class="space-y-6">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 text-sm">Organizaciones Verificadas</span>
                            <span class="font-bold text-slate-200 bg-slate-850 border border-slate-700 px-3 py-1 rounded-lg text-sm">Activas</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 text-sm">Filtros y Búsqueda</span>
                            <span class="font-bold text-slate-200 bg-cyan-500/10 text-cyan-300 border border-cyan-500/20 px-3 py-1 rounded-lg text-sm">Optimizado</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 text-sm">Soporte y Control</span>
                            <span class="font-bold text-slate-200 bg-amber-500/10 text-amber-300 border border-amber-500/20 px-3 py-1 rounded-lg text-sm">Garantizado</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Features Section --}}
    <section class="py-16 border-t border-slate-900 bg-slate-950/20 rounded-3xl p-8 backdrop-blur-sm">
        <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
            <h2 class="text-3xl font-extrabold text-white tracking-tight">Conectando Propósito y Crecimiento</h2>
            <p class="text-slate-400 font-light">Una solución integral para la búsqueda de empleo y la promoción de emprendimientos con valores compartidos.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            {{-- Feature 1 --}}
            <div class="bg-slate-900/40 border border-slate-800/80 rounded-2xl p-6 hover:border-cyan-500/40 hover:-translate-y-1 transition-all duration-300 group backdrop-blur-sm shadow-sm">
                <div class="w-12 h-12 rounded-xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center mb-5 text-xl">
                    💼
                </div>
                <h3 class="text-lg font-bold text-slate-200 mb-2">Bolsa de Trabajo</h3>
                <p class="text-slate-400 text-sm leading-relaxed font-light">
                    Explora ofertas de empleo, postula de forma ágil y segura, y conecta con organizaciones comprometidas que buscan tu talento profesional y declaración de fe.
                </p>
            </div>

            {{-- Feature 2 --}}
            <div class="bg-slate-900/40 border border-slate-800/80 rounded-2xl p-6 hover:border-amber-500/40 hover:-translate-y-1 transition-all duration-300 group backdrop-blur-sm shadow-sm">
                <div class="w-12 h-12 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center mb-5 text-xl">
                    💡
                </div>
                <h3 class="text-lg font-bold text-slate-200 mb-2">Directorio de Emprendimientos</h3>
                <p class="text-slate-400 text-sm leading-relaxed font-light">
                    Promueve tu negocio local, ofrece tus servicios profesionales a la comunidad, incrementa tu red de contactos y fomenta la economía colaborativa de manera nativa.
                </p>
            </div>

            {{-- Feature 3 --}}
            <div class="bg-slate-900/40 border border-slate-800/80 rounded-2xl p-6 hover:border-teal-500/40 hover:-translate-y-1 transition-all duration-300 group backdrop-blur-sm shadow-sm">
                <div class="w-12 h-12 rounded-xl bg-teal-500/10 border border-teal-500/20 flex items-center justify-center mb-5 text-xl">
                    🛡️
                </div>
                <h3 class="text-lg font-bold text-slate-200 mb-2">Seguridad y Confianza</h3>
                <p class="text-slate-400 text-sm leading-relaxed font-light">
                    Plataforma completamente segura y moderada de forma rigurosa. Garantizamos la integridad y la confidencialidad de tus datos mediante la verificación de perfiles y empresas.
                </p>
            </div>
        </div>
    </section>
</x-public.layout>