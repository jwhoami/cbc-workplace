<x-mail::message>
# {{ __('mail/application-received.greeting') }}

{!! __('mail/application-received.intro', [
    'listing_title' => $listing->title,
    'organization_name' => $organization->display_name ?? $organization->legal_name,
]) !!}

{!! __('mail/application-received.candidate', ['candidate_name' => $candidateName]) !!}

{{ __('mail/application-received.submitted_at', ['submitted_at' => $submittedAt->format('d/m/Y H:i')]) }}

<x-mail::button :url="url('/member/job-listings/' . $listing->id)">
{{ __('mail/application-received.cta') }}
</x-mail::button>

{{ __('mail/application-received.thanks') }}

{{ config('app.name') }}
</x-mail::message>
