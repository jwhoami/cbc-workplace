<x-mail::message>
  # Solicitud de afiliación

  Su solicitud fue aprobada.

  # Membership Application

  Your membership application has been approved.

  {{ $member->membership_approval_reason }}

  Bendiciones/Blessings,<br>
  {{ config('app.name') }}
</x-mail::message>
