<x-mail::message>
  # Solicitud de Afiliación

  El miembro {{ $member->name }} con email {{ $member->email }} está solicitando afiliación.

  Para aprobar esta solicitud haga clic en el botón de Acceder

  Member {{ $member->name }} with email {{ $member->email }} is requesting membership.

  To approve this request, click the Login button.

  <x-mail::button :url="url('/admin/members?activeTab=Solicitudes')">
    Acceder
  </x-mail::button>

  Bendiciones,<br>
  {{ config('app.name') }}
</x-mail::message>
