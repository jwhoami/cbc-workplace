<x-public.layout :title="__('public.too_many_requests.title')" :noindex="true">
    <x-public.error-state
        :title="__('public.too_many_requests.title')"
        :message="__('public.too_many_requests.message')"
        :retryUrl="url('/bolsa-de-trabajo')"
    />
</x-public.layout>
