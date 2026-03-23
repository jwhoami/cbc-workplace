<x-mail::message>
  # Su solicitud de afiliación

  Su solicitud fue rechazada por la siguiente razón:

  {{ $member->membership_approval_reason }}

  # Membership Application

  Your membership application has been denied for the following reason.

  {{ $member->membership_approval_reason }}

  Bendiciones/Blessings,<br>
  {{ config('app.name') }}
</x-mail::message>
