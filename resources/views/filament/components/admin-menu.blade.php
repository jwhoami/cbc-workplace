@if (auth()->user()->isAdmin())
    <x-filament::dropdown placement="bottom-end" teleport>
        <x-slot name="trigger">
            <button
                class="dark:bg-gray-950 flex flex-shrink-0 w-10 h-10 rounded-full bg-gray-200 items-center justify-center"
                aria-label="{{ __('filament-panels::layout.actions.open_user_menu.label') }}" type="button">
                <x-heroicon-o-user-circle class="w-4 h-4" />
            </button>
        </x-slot>

        <x-filament::dropdown.list>
            @foreach ($items as $key => $item)
                <x-filament::dropdown.list.item :color="$item->getColor()" :href="$item->getUrl()" :icon="$item->getIcon()" tag="a">
                    {{ $item->getLabel() }}
                </x-filament::dropdown.list.item>
            @endforeach
        </x-filament::dropdown.list>
    </x-filament::dropdown>
@endif
