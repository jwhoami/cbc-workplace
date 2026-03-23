<x-mail::message>
  # Aprobación de Solicitud de Emprendimiento

  Su emprendimiento fue aprobado.

  {{ $venture->approval_reason }}

  # Entrepreneurship Application Approval

  Your entrepreneurship was approved.

  {{ $venture->approval_reason }}

  Gracias/Thankyou


  {{ config('app.name') }}
</x-mail::message>
