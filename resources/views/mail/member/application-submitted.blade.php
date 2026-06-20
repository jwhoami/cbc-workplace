<x-mail::message>
# {{ __('mail/application-submitted.greeting', ['name' => $candidateName]) }}

{!! __('mail/application-submitted.intro', [
    'listing_title' => $listing->title,
    'organization_name' => $organization->display_name ?? $organization->legal_name,
]) !!}

{{ __('mail/application-submitted.body') }}

<x-mail::button :url="url('/member/applications')">
{{ __('mail/application-submitted.cta') }}
</x-mail::button>

{{ __('mail/application-submitted.thanks') }}

{{ config('app.name') }}
</x-mail::message>
