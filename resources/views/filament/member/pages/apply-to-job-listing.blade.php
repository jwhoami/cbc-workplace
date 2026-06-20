<x-filament-panels::page>
    @if (! $isSubmittable)
        <x-filament::section>
            <p class="text-danger-600">
                {{ __('models/application.notifications.listing_inactive') }}
            </p>
        </x-filament::section>
    @else
        <form wire:submit="submit" class="space-y-6">
            {{ $this->form }}

            <div class="flex items-center justify-end gap-3">
                <x-filament::button
                    type="button"
                    color="gray"
                    tag="a"
                    href="{{ url('/member/applications') }}"
                >
                    {{ __('models/application.form.cancel') }}
                </x-filament::button>

                <x-filament::button type="submit" color="primary">
                    {{ __('models/application.form.submit') }}
                </x-filament::button>
            </div>
        </form>
    @endif
</x-filament-panels::page>
