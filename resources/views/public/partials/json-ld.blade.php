@php
    $jobPosting = [
        '@context' => 'https://schema.org/',
        '@type' => 'JobPosting',
        'title' => $offer->title,
        'description' => strip_tags((string) $offer->description),
        'datePosted' => optional($offer->published_at)->toIso8601String(),
        'employmentType' => $offer->contract_type?->name,
        'hiringOrganization' => $offer->organization ? [
            '@type' => 'Organization',
            'name' => $offer->organization->display_name,
            'sameAs' => $offer->organization->website,
            'logo' => $offer->organization->logo
                ? \Illuminate\Support\Facades\Storage::url($offer->organization->logo)
                : null,
        ] : null,
        'jobLocation' => $offer->city ? [
            '@type' => 'Place',
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => $offer->city,
                'addressRegion' => $offer->province,
                'addressCountry' => 'PA',
            ],
        ] : null,
        'validThrough' => optional($offer->application_deadline)->toIso8601String(),
        'baseSalary' => ($offer->salary_min !== null || $offer->salary_max !== null) ? [
            '@type' => 'MonetaryAmount',
            'currency' => $offer->currency,
            'value' => [
                '@type' => 'QuantitativeValue',
                'minValue' => $offer->salary_min ? (float) $offer->salary_min : null,
                'maxValue' => $offer->salary_max ? (float) $offer->salary_max : null,
                'unitText' => 'MONTH',
            ],
        ] : null,
    ];

    $jobPosting = array_filter(
        $jobPosting,
        static fn ($value) => $value !== null && $value !== ''
    );
@endphp

<script type="application/ld+json">
    {!! json_encode($jobPosting, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
