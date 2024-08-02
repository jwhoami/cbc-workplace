<x-mail::message>
# Su solicitud de afiliación

Su solicitud fue rechazada por la siguiente razón:

{{ $member->membership_approval_reason }}

  Bendiciones,<br>
{{ config('app.name') }}
</x-mail::message>
