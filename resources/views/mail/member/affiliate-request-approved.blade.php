<x-mail::message>
# Solicitud de afiliación

Su solicitud fue aprobada.

{{ $member->membership_approval_reason }}

Bendiciones,<br>
{{ config('app.name') }}
</x-mail::message>
