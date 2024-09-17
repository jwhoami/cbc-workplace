<x-mail::message>
# Solicitud de Afiliación

El miembro {{ $member->name }} con email {{ $member->email }} está solicitando afiliación al portal.

Para aprobar esta solicitud haga clic en el botón de Acceder

<x-mail::button :url="url('/admin/members?activeTab=Solicitudes')">
Acceder
</x-mail::button>

Bendiciones,<br>
{{ config('app.name') }}
</x-mail::message>
