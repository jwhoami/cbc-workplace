<x-mail::message>
  # Organización Suspendida

  Le informamos que su organización **{{ $organization->display_name }}** ha sido suspendida en la plataforma.

  **Motivo**: {{ $reason }}

  Si considera que esta decisión es incorrecta, puede solicitar una nueva verificación desde su panel de miembro.

  <x-mail::button :url="url('/member')">
  Acceder a mi panel
  </x-mail::button>

  Gracias/Thankyou

  {{ config('app.name') }}
</x-mail::message>
