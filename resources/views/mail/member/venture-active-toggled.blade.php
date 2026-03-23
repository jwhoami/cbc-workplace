<x-mail::message>
# Cambio en su emprendimiento

Su emprendimiento fue {{ ($venture->is_active ? "activado" : "inactivado") }}

  Bendiciones,<br>
{{ config('app.name') }}
</x-mail::message>
