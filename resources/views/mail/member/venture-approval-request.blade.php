<x-mail::message>
# Solicitud de Aprobación de Emprendimiento

El miembro {{ $venture->member->name }} con email {{ $venture->member->email }} esta solicitando
aprobación de su emprendimiento titulado

## {{ $venture->title }}.

Para aprobar esta solicitud haga clic en el botón de Acceder

<x-mail::button :url="url('/admin/ventures?activeTab=En+Aprobación')">
Acceder
</x-mail::button>

  Bendiciones,<br>
{{ config('app.name') }}
</x-mail::message>
