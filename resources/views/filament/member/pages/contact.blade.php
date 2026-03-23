<x-filament-panels::page>
  <x-filament-panels::form id="form" wire:submit="submit">
    {{ $this->form }}

    <x-filament-panels::form.actions :actions="$this->getCachedFormActions()" />
  </x-filament-panels::form>
</x-filament-panels::page>