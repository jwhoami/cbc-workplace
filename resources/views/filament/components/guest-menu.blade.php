@php
$invitationCodeRequiredForRegistration = \App\Models\Config::make()->getp('invitationCodeRequiredForRegistration', false);
@endphp
@if(! $invitationCodeRequiredForRegistration)
<x-filament::link :href="route('filament.member.auth.register')">
    {{ __('Registrar') }}
</x-filament::link>
@endif
<x-filament::link :href="route('filament.member.auth.login')">
    {{ __('Acceder') }}
</x-filament::link>
