<x-filament-panels::page.simple>
    <div class="flex flex-col gap-y-6">
        @if($this->getText())
            <div class="prose max-w-none text-center dark:prose-invert text-gray-600 dark:text-gray-400">
                {!! $this->getText() !!}
            </div>
        @endif

        @if(auth('member')->check())
            @php
                $member = auth('member')->user();
            @endphp

            @if(! $member->hasVerifiedEmail())
                <!-- Tarjeta de Confirmación de Correo Pendiente -->
                <div class="rounded-xl border border-amber-200 bg-amber-50/50 p-6 dark:border-amber-800/30 dark:bg-amber-950/10">
                    <div class="flex items-start gap-4">
                        <div class="rounded-lg bg-amber-100 p-2 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400">
                            <!-- Heroicon-o-envelope -->
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                            </svg>
                        </div>
                        <div class="flex-1 space-y-2 text-left">
                            <h3 class="text-lg font-semibold text-amber-900 dark:text-amber-200">
                                {{ __('Confirma tu correo electrónico') }}
                            </h3>
                            <p class="text-sm text-amber-700 dark:text-amber-300">
                                {{ __('Para continuar y acceder al portal, debes verificar tu cuenta. Hemos enviado un correo de confirmación a:') }}
                            </p>
                            <div class="inline-block px-3 py-1 bg-white dark:bg-gray-800 border border-amber-200 dark:border-gray-700 rounded-md font-mono text-sm font-semibold text-gray-850 dark:text-gray-200 select-all shadow-sm">
                                {{ $member->email }}
                            </div>
                            <div class="text-xs text-amber-600 dark:text-amber-400 pt-2 space-y-1">
                                <p>• {{ __('¿No lo encuentras? Revisa tu carpeta de correo no deseado (Spam).') }}</p>
                                <p>• {{ __('El enlace expira en 60 minutos.') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col sm:flex-row gap-3 justify-end border-t border-amber-200/50 dark:border-amber-800/20 pt-4">
                        <x-filament::button
                            wire:click="resendVerification"
                            wire:loading.attr="disabled"
                            color="warning"
                            icon="heroicon-m-arrow-path"
                            tag="button"
                            size="sm"
                        >
                            <span wire:loading.remove wire:target="resendVerification">
                                {{ __('Reenviar correo') }}
                            </span>
                            <span wire:loading wire:target="resendVerification">
                                {{ __('Reenviando...') }}
                            </span>
                        </x-filament::button>

                        <x-filament::button
                            href="/member"
                            color="primary"
                            icon="heroicon-m-arrow-right"
                            tag="a"
                            size="sm"
                        >
                            {{ __('Ir al Panel') }}
                        </x-filament::button>
                    </div>
                </div>
            @else
                <!-- Tarjeta de Cuenta Verificada -->
                <div class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-6 dark:border-emerald-800/30 dark:bg-emerald-950/10">
                    <div class="flex items-start gap-4">
                        <div class="rounded-lg bg-emerald-100 p-2 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400">
                            <!-- Heroicon-o-check-circle -->
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                        <div class="flex-1 space-y-1 text-left">
                            <h3 class="text-lg font-semibold text-emerald-900 dark:text-emerald-200">
                                {{ __('¡Cuenta verificada!') }}
                            </h3>
                            <p class="text-sm text-emerald-700 dark:text-emerald-300">
                                {{ __('Tu dirección de correo ya ha sido confirmada con éxito. Ya puedes acceder al portal completo.') }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end border-t border-emerald-200/50 dark:border-emerald-800/20 pt-4">
                        <x-filament::button
                            href="/member"
                            color="success"
                            icon="heroicon-m-arrow-right"
                            tag="a"
                            size="sm"
                        >
                            {{ __('Entrar al Panel') }}
                        </x-filament::button>
                    </div>
                </div>
            @endif

            <!-- Enlace de Cierre de Sesión -->
            <div class="flex justify-center mt-2">
                <button
                    wire:click="logout"
                    class="text-xs text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 flex items-center gap-1.5 transition duration-150 ease-in-out font-medium"
                >
                    <!-- Heroicon-o-arrow-left-on-rectangle -->
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
                    </svg>
                    {{ __('Cerrar sesión o registrarse con otra cuenta') }}
                </button>
            </div>
        @else
            <!-- Fallback para no autenticados -->
            <div class="mt-6 flex justify-center gap-4">
                <x-filament::button
                    :href="route('filament.member.auth.login')"
                    tag="a"
                >
                    {{ __('Iniciar sesión') }}
                </x-filament::button>
            </div>
        @endif
    </div>
</x-filament-panels::page.simple>

