<x-mail::message>
  # Solicitud de Verificación de Organización

  El miembro {{ $organization->member->name }} con email {{ $organization->member->email }} está
  solicitando la verificación de su organización

  ## {{ $organization->display_name }}

  **Nombre Legal**: {{ $organization->legal_name }}
  **Tipo**: {{ $organization->type->getLabel() }}

  Para revisar esta solicitud haga clic en el botón de Acceder

  <x-mail::button :url="url('/admin/organizations')">
  Acceder
  </x-mail::button>

  Gracias/Thankyou

  {{ config('app.name') }}
</x-mail::message>
