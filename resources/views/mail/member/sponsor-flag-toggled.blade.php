<x-mail::message>
# Cambio en su perfil

Su privilegio de patrocinar esta {{ ($member->can_sponsor ? "activo" : "inactivo") }}

{{ $data['reason'] }}

  Bendiciones,<br>
{{ config('app.name') }}
</x-mail::message>
