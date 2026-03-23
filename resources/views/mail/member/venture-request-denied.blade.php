<x-mail::message>
  # Aprobación de Solicitud de Emprendimiento

  Su emprendimiento fue declinado.

  {{ $venture->approval_reason }}

  # Entrepreneurship Application Approval

  Your entrepreneurship was denied.

  {{ $venture->approval_reason }}


  Bendiciones/Blessings,<br>
  {{ config('app.name') }}
</x-mail::message>
