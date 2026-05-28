<x-filament-panels::page>
    <div class="wiki-grid">
        
        <!-- Sidebar Izquierdo -->
        <div class="wiki-sidebar {{ $showSidebarOnMobile ? 'mobile-show' : 'mobile-hide' }} bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl p-4 flex flex-col gap-4 transition-shadow duration-500 shadow-[0_0_25px_rgba(6,182,212,0.03)] hover:shadow-[0_0_30px_rgba(6,182,212,0.06)]">
            
            <div class="flex items-center justify-between pb-2 border-b border-slate-800/60 mb-1">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <span class="text-xs font-semibold text-cyan-400 uppercase tracking-widest">{{ __('Índice') }}</span>
                </div>
                <span class="text-[10px] text-slate-500 font-mono">{{ count($this->getChapters()) }} {{ __('Temas') }}</span>
            </div>

            <!-- Listado de Capítulos -->
            <div class="flex flex-col gap-1.5">
                @forelse ($this->getChapters() as $ch)
                    <button 
                        wire:click="selectChapter('{{ $ch['slug'] }}')"
                        class="group w-full text-left flex items-center justify-between py-2.5 px-4 rounded-xl border transition-all duration-300 hover:translate-x-1.5 active:scale-[0.98] {{ $this->chapter === $ch['slug'] ? 'text-cyan-400 bg-cyan-950/20 border-cyan-800/50 shadow-[0_0_15px_-3px_rgba(6,182,212,0.2)] font-semibold' : 'text-slate-400 bg-transparent border-transparent hover:text-slate-200 hover:bg-slate-800/40' }}"
                    >
                        <span class="truncate pr-2 text-sm">{{ $ch['title'] }}</span>
                        <svg class="w-4 h-4 shrink-0 transition-transform duration-300 {{ $this->chapter === $ch['slug'] ? 'translate-x-1 text-cyan-400' : 'text-slate-600 group-hover:translate-x-1' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                @empty
                    <div class="text-center py-8 text-slate-500">
                        <svg class="w-12 h-12 mx-auto mb-2 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <p class="text-sm font-medium">{{ __('No se encontraron resultados') }}</p>
                        <p class="text-xs text-slate-600 mt-1">{{ __('Intente con otros términos') }}</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Lector Derecho -->
        <div class="wiki-reader {{ !$showSidebarOnMobile ? 'mobile-show' : 'mobile-hide' }} flex flex-col gap-6">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl p-6 md:p-8 flex flex-col gap-6 shadow-[0_0_30px_rgba(0,0,0,0.4)]">
                
                <!-- Encabezado Móvil de Retorno -->
                @if (!$showSidebarOnMobile)
                    <div class="lg:hidden flex items-center justify-between pb-3 border-b border-slate-800/80 mb-2">
                        <button 
                            wire:click="showMobileMenu" 
                            class="flex items-center gap-1.5 text-xs text-cyan-400 font-semibold py-1.5 px-3 bg-cyan-950/40 border border-cyan-800/40 rounded-xl hover:bg-cyan-900/40 active:scale-95 transition-all duration-200"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            <span>{{ __('Volver al Índice') }}</span>
                        </button>
                        <span class="text-[10px] text-slate-500 font-semibold uppercase tracking-wider">{{ __('Lector') }}</span>
                    </div>
                @endif

                <!-- Cuerpo de la Wiki -->
                <div class="wiki-content">
                    {!! $this->getActiveContent() !!}
                </div>

                <hr class="border-slate-800/60 mt-4">

                <!-- Paginación Inferior -->
                <div class="flex items-center justify-between pt-2">
                    <div>
                        @if ($prev = $this->getPreviousChapter())
                            <button 
                                wire:click="selectChapter('{{ $prev['slug'] }}')"
                                class="flex items-center gap-2 text-sm text-slate-400 hover:text-cyan-400 transition-colors py-2 px-3 hover:bg-slate-800/40 rounded-xl text-left active:scale-95 duration-200"
                            >
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                                <div>
                                    <div class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold">{{ __('Anterior') }}</div>
                                    <div class="font-medium truncate max-w-[120px] md:max-w-[200px] text-xs md:text-sm">{{ $prev['title'] }}</div>
                                </div>
                            </button>
                        @endif
                    </div>
                    <div>
                        @if ($next = $this->getNextChapter())
                            <button 
                                wire:click="selectChapter('{{ $next['slug'] }}')"
                                class="flex items-center gap-2 text-sm text-slate-400 hover:text-cyan-400 transition-colors py-2 px-3 hover:bg-slate-800/40 rounded-xl text-right active:scale-95 duration-200"
                            >
                                <div>
                                    <div class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold">{{ __('Siguiente') }}</div>
                                    <div class="font-medium truncate max-w-[120px] md:max-w-[200px] text-xs md:text-sm">{{ $next['title'] }}</div>
                                </div>
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- Estilos Inline Defensivos Premium -->
    <style>
        .wiki-grid {
            display: grid !important;
            grid-template-columns: 1fr !important;
            gap: 1.5rem !important;
            align-items: start !important;
        }

        /* Comportamiento para Escritorio (Pantallas >= 1024px) */
        @media (min-width: 1024px) {
            .wiki-grid {
                grid-template-columns: 340px 1fr !important; /* Sidebar: 340px, Lector: el resto */
            }
            .wiki-sidebar {
                display: block !important;
                position: sticky !important;
                top: 6rem !important;
                max-height: calc(100vh - 10rem) !important;
                overflow-y: auto !important;
            }
            .wiki-reader {
                display: block !important;
            }
        }

        /* Comportamiento Conmutado para Móviles / Tablets (Pantallas < 1024px) */
        @media (max-width: 1023px) {
            .wiki-sidebar.mobile-hide {
                display: none !important;
            }
            .wiki-sidebar.mobile-show {
                display: block !important;
            }
            .wiki-reader.mobile-hide {
                display: none !important;
            }
            .wiki-reader.mobile-show {
                display: block !important;
            }
        }

        /* Tipografía del Lector */
        .wiki-content h1 {
            font-size: 1.875rem !important;
            line-height: 2.25rem !important;
            font-weight: 700 !important;
            color: #22d3ee !important; /* Cyan 400 */
            border-bottom: 1px solid #1e293b !important;
            padding-bottom: 0.5rem !important;
            margin-top: 0.5rem !important;
            margin-bottom: 1.5rem !important;
        }
        .wiki-content h2 {
            font-size: 1.25rem !important;
            line-height: 1.75rem !important;
            font-weight: 600 !important;
            color: #e2e8f0 !important; /* Slate 200 */
            margin-top: 2rem !important;
            margin-bottom: 0.75rem !important;
        }
        .wiki-content h3 {
            font-size: 1.125rem !important;
            line-height: 1.75rem !important;
            font-weight: 600 !important;
            color: #cbd5e1 !important; /* Slate 300 */
            margin-top: 1.5rem !important;
            margin-bottom: 0.5rem !important;
        }
        .wiki-content p {
            color: #94a3b8 !important; /* Slate 400 */
            line-height: 1.625 !important;
            margin-bottom: 1rem !important;
        }
        .wiki-content ul {
            list-style-type: disc !important;
            margin-left: 1.5rem !important;
            margin-bottom: 1rem !important;
            color: #94a3b8 !important;
        }
        .wiki-content ol {
            list-style-type: decimal !important;
            margin-left: 1.5rem !important;
            margin-bottom: 1rem !important;
            color: #94a3b8 !important;
        }
        .wiki-content li {
            margin-bottom: 0.25rem !important;
        }
        .wiki-content strong {
            color: #f8fafc !important; /* Slate 50 */
            font-weight: 600 !important;
        }
        .wiki-content img {
            border-radius: 0.75rem !important;
            border: 1px solid #1e293b !important;
            margin-top: 1.5rem !important;
            margin-bottom: 1.5rem !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3) !important;
            max-width: 100% !important;
            height: auto !important;
            display: block !important;
            margin-left: auto !important;
            margin-right: auto !important;
        }
        .wiki-content blockquote {
            border-left-width: 4px !important;
            border-left-color: #06b6d4 !important; /* Cyan 500 */
            background-color: rgba(6, 182, 212, 0.05) !important;
            padding: 0.75rem 1rem !important;
            border-top-right-radius: 0.5rem !important;
            border-bottom-right-radius: 0.5rem !important;
            margin-top: 1rem !important;
            margin-bottom: 1rem !important;
        }
        .wiki-content blockquote p {
            margin-bottom: 0 !important;
            font-style: italic !important;
            color: #22d3ee !important;
        }
        .wiki-content table {
            width: 100% !important;
            border-collapse: collapse !important;
            margin-top: 1.5rem !important;
            margin-bottom: 1.5rem !important;
        }
        .wiki-content th {
            background-color: #0f172a !important; /* Slate 900 */
            color: #cbd5e1 !important;
            font-weight: 600 !important;
            text-align: left !important;
            padding: 0.75rem 1rem !important;
            border-width: 1px !important;
            border-color: #1e293b !important;
        }
        .wiki-content td {
            padding: 0.75rem 1rem !important;
            border-width: 1px !important;
            border-color: #1e293b !important;
            color: #94a3b8 !important;
        }
        .wiki-content tr:nth-child(even) {
            background-color: rgba(30, 41, 59, 0.2) !important;
        }
        .wiki-content kbd {
            background-color: #1e293b !important;
            border: 1px solid #334155 !important;
            border-radius: 0.25rem !important;
            padding: 0.125rem 0.25rem !important;
            font-size: 0.75rem !important;
            font-family: monospace !important;
            color: #cbd5e1 !important;
        }
    </style>
</x-filament-panels::page>
