<x-filament-panels::page>
  <div>
    <form wire:submit="save">
      {{ $this->form }}

      <div class="flex mt-2 justify-center">
        <x-filament::button wire:click="save">
          {{ __("Guardar") }}
        </x-filament::button>
      </div>

    </form>

    <x-filament-actions::modals />
  </div>
</x-filament-panels::page>