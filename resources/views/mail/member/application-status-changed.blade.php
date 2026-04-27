<x-mail::message>
# {{ __('mail/application-status-changed.greeting', ['name' => $candidateName]) }}

{!! __('mail/application-status-changed.intro', [
    'listing_title' => $listing->title,
    'organization_name' => $organization->display_name ?? $organization->legal_name,
    'status_label' => $currentLabel,
]) !!}

{{ __($bodyKey) }}

<x-mail::button :url="url('/member/applications')">
{{ __('mail/application-status-changed.cta') }}
</x-mail::button>

{{ __('mail/application-status-changed.thanks') }}

{{ config('app.name') }}
</x-mail::message>
