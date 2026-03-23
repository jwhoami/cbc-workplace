<x-mail::message>
  # ¡Organización Verificada!

  Nos complace informarle que su organización **{{ $organization->display_name }}** ha sido verificada exitosamente en la plataforma.

  Ahora puede acceder a todas las funcionalidades disponibles para organizaciones verificadas.

  <x-mail::button :url="url('/member')">
  Acceder a mi panel
  </x-mail::button>

  Gracias/Thankyou

  {{ config('app.name') }}
</x-mail::message>
