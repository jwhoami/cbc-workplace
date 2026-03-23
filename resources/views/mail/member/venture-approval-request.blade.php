<x-mail::message>
  # Solicitud de Aprobación de Emprendimiento

  El miembro {{ $venture->member->name }} con email {{ $venture->member->email }} está
  solicitando aprobación de su emprendimiento titulado

  ## {{ $venture->title }}.

  Para aprobar esta solicitud haga clic en el botón de Acceder

  Member {{ $venture->member->name }} with email {{ $venture->member->email }} is
  requesting approval for their entrepreneurship titled

  ## {{ $venture->title }}.

  To approve this request, click the Login button.

  <x-mail::button :url="url('/admin/ventures?activeTab=En+Aprobación')">
  Acceder
  </x-mail::button>


  Gracias/Thankyou

  {{ config('app.name') }}
</x-mail::message>
