<x-mail::message>
  Estimado(a) {{ $data['name'] }},

  Usted ha sido invitado por {{ $user?->name }} para registrarse en {{ config('app.name') }}.

  Esta invitación vencerá en 72 horas.

  You have been invited by {{ $user?->name }} to join {{ config('app.name') }}.

  This invitation will expire in 72 hours.

  <x-mail::button :url="url($url)">
    {{ __("Registrar") }}
  </x-mail::button>

  Gracias

  {{ config('app.name') }}

</x-mail::message>
