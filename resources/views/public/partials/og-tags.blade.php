@php
    $ogDescription = \Illuminate\Support\Str::limit(strip_tags((string) $offer->description), 200);
    $ogImage = $offer->organization?->logo
        ? \Illuminate\Support\Facades\Storage::url($offer->organization->logo)
        : null;
@endphp

<meta property="og:type" content="article">
<meta property="og:title" content="{{ $offer->title }}">
<meta property="og:description" content="{{ $ogDescription }}">
<meta property="og:url" content="{{ $detailUrl }}">
@if ($ogImage)
    <meta property="og:image" content="{{ $ogImage }}">
@endif
<meta property="og:locale" content="es_ES">
<meta property="og:site_name" content="Lazos de Fe">

<meta name="twitter:card" content="{{ $ogImage ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $offer->title }}">
<meta name="twitter:description" content="{{ $ogDescription }}">
@if ($ogImage)
    <meta name="twitter:image" content="{{ $ogImage }}">
@endif
