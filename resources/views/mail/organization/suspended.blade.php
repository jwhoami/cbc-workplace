<x-mail::message>
# {{ __('mail/organization/suspended.greeting', ['name' => $organization->display_name]) }}

{{ __('mail/organization/suspended.body.paragraph_1') }}

{{ __('mail/organization/suspended.body.paragraph_2') }}

<x-mail::button :url="config('app.url') . '/contact'">
{{ __('mail/organization/suspended.cta') }}
</x-mail::button>

{{ __('mail/organization/suspended.signoff') }}<br>
{{ config('app.name') }}
</x-mail::message>
