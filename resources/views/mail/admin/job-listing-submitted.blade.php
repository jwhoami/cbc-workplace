<x-mail::message>
  # Nueva Oferta de Empleo Pendiente

  La organización **{{ $jobListing->organization->display_name }}** ha enviado una oferta de empleo para aprobación.

  **Puesto:** {{ $jobListing->title }}

  Por favor revise la oferta en el panel de administración.

  # New Job Listing Pending Approval

  The organization **{{ $jobListing->organization->display_name }}** has submitted a job listing for approval.

  **Position:** {{ $jobListing->title }}

  Please review the listing in the admin panel.

  Gracias/Thank you

  {{ config('app.name') }}
</x-mail::message>
