<x-mail::message>
# Cambio en su perfil

Su registro esta {{ ($member->is_active ? "activo" : "inactivo") }}

{{ $data['reason'] }}

Bendiciones,<br>
{{ config('app.name') }}
</x-mail::message>
