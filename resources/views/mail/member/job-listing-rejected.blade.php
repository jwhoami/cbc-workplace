<x-mail::message>
  # Oferta de Empleo Rechazada

  Su oferta de empleo **{{ $jobListing->title }}** ha sido rechazada.

  **Motivo:** {{ $jobListing->approval_reason }}

  Puede editar su oferta y enviarla nuevamente para aprobación.

  # Job Listing Rejected

  Your job listing **{{ $jobListing->title }}** has been rejected.

  **Reason:** {{ $jobListing->approval_reason }}

  You can edit your listing and resubmit it for approval.

  Gracias/Thank you

  {{ config('app.name') }}
</x-mail::message>
