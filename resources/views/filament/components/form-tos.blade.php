<div>
  @php
  $tos = \App\Models\Text::getText('terminos-y-condiciones');
  [$title, $content] = count($tos) === 2 ? $tos : ['', ''];
  @endphp
  <label class="flex items-center gap-3">
    <x-filament::input.checkbox wire:model="{{ $getStatePath() }}" />

    <div>
      <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
        Acepto los
      </span>
      <x-filament::modal width="5xl">
        <x-slot name="trigger">
          <a class="text-sm font-medium leading-6 text-gray-950 dark:text-white underline" href="javascript:void(0)">terminos y condiciones</a>
        </x-slot>

        {!! $content !!}
      </x-filament::modal>
    </div>
  </label>
</div>