<x-mail::message>
Estimado(a) {{ $data['name'] }},

Usted ha sido invitado por {{ $user?->name }} para afiliarse a {{ config('app.name') }}.

Esta invitación vencerá en 72 horas.

<x-mail::button :url="url($url)">
Afiliar
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
