<x-mail::message>
  # Oferta de Empleo Aprobada

  Su oferta de empleo **{{ $jobListing->title }}** ha sido aprobada y ahora es visible públicamente.

  @if($jobListing->approval_reason)
  **Comentario:** {{ $jobListing->approval_reason }}
  @endif

  # Job Listing Approved

  Your job listing **{{ $jobListing->title }}** has been approved and is now publicly visible.

  @if($jobListing->approval_reason)
  **Comment:** {{ $jobListing->approval_reason }}
  @endif

  Gracias/Thank you

  {{ config('app.name') }}
</x-mail::message>
