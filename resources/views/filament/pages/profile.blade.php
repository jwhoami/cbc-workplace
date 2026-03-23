<div>
  <header
    class="space-y-2 items-start justify-between sm:flex sm:space-y-0 sm:space-x-4  sm:rtl:space-x-reverse sm:py-4 filament-header">
    <h1 class="text-2xl font-bold tracking-tight md:text-3xl filament-header-heading">
      {{ __('Perfil') }}
    </h1>
  </header>
  <form wire:submit.prevent="save" class="space-y-8">
    <div>
      <div class="flex justify-end space-x-2">
        <a class="inline-flex items-center justify-center py-1 gap-1 font-medium rounded-lg border transition-colors focus:outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset filament-button dark:focus:ring-offset-0 min-h-[2.25rem] px-4 text-sm text-gray-800 bg-white border-gray-300 hover:bg-gray-50 focus:ring-primary-600 focus:text-primary-600 focus:bg-primary-50 focus:border-primary-600 dark:bg-gray-800 dark:border-gray-600 dark:hover:border-gray-500 dark:text-gray-200 dark:focus:text-primary-400 dark:focus:border-primary-400 dark:focus:bg-gray-800 filament-page-button-action" href="{{ route('filament.pages.dashboard') }}">
          <span class="">
            {{ __('Volver') }}
          </span>
        </a>
        <x-filament::button type="submit" form="profile" class="">
          <span class="">
            {{ __('Guardar') }}
          </span>  
        </x-filament::button>
    </div>
    {{ $this->form }}

  </div>

  </form>
</div>
