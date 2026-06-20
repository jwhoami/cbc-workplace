@props(['organization'])

<div role="status" aria-live="polite"
     class="suspended-org-banner mb-4 rounded-lg border border-danger-300 bg-danger-50 px-4 py-3 text-danger-900 dark:border-danger-700 dark:bg-danger-950 dark:text-danger-200">
    <strong class="block text-sm font-semibold">
        {{ __('models/organization.banner.suspended.title') }}
    </strong>
    <p class="mt-1 text-sm">
        {{ __('models/organization.banner.suspended.body') }}
    </p>
</div>
