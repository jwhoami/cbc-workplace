<x-mail::message>
# {{ __('mail/job-alert.instant.greeting', ['name' => $alert->member->name]) }}

{{ __('mail/job-alert.instant.intro') }}

@foreach ($offers as $offer)
## {{ $offer->title }}

- **{{ __('mail/job-alert.offer.organization') }}:** {{ $offer->organization?->display_name ?? '—' }}
- **{{ __('mail/job-alert.offer.category') }}:** {{ $offer->categories->pluck('name')->join(', ') ?: '—' }}
- **{{ __('mail/job-alert.offer.city') }}:** {{ $offer->city ?? '—' }}
- **{{ __('mail/job-alert.offer.modality') }}:** {{ $offer->work_modality?->getLabel() ?? '—' }}

<x-mail::button :url="route('public.job-offer.show', $offer->slug)">
{{ __('mail/job-alert.offer.view') }}
</x-mail::button>

---
@endforeach

{{ __('mail/job-alert.closing') }}

{{ __('mail/job-alert.signature') }}

<small>
    <a href="{{ $unsubscribeUrl }}">{{ __('mail/job-alert.unsubscribe.cta') }}</a>
</small>
</x-mail::message>
