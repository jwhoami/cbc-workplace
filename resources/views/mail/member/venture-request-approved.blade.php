<x-mail::message>
  # Aprobación de Solicitud de Emprendimiento

  Su emprendimiento fue aprobado.

  {{ $venture->approval_reason }}

  Gracias


  {{ config('app.name') }}
</x-mail::message>
