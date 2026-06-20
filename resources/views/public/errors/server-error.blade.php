<x-public.layout :title="__('public.error.title')" :noindex="true">
    <x-public.error-state
        :title="__('public.error.title')"
        :message="__('public.error.message')"
        :retryUrl="url()->full()"
    />
</x-public.layout>
