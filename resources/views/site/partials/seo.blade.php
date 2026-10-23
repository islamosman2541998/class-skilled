{{-- Open Graph, Twitter card, search engine verification and JSON-LD (SeoManager + SchemaBuilder). --}}
@php
    $seoSettings = app(\App\Settings\SeoSettings::class);
    $ogImage = $seo->image();
    $ogLocale = app()->getLocale() === 'ar' ? 'ar_AE' : 'en_US';
@endphp
<meta property="og:type" content="{{ $seo->type() }}">
<meta property="og:site_name" content="{{ $site->name() }}">
<meta property="og:title" content="{{ $seo->title() }}">
<meta property="og:description" content="{{ $seo->description() }}">
<meta property="og:url" content="{{ $seo->canonical() }}">
<meta property="og:locale" content="{{ $ogLocale }}">
@foreach ($seo->availableLocales() as $alt)
    @if ($alt !== app()->getLocale())
        <meta property="og:locale:alternate" content="{{ $alt === 'ar' ? 'ar_AE' : 'en_US' }}">
    @endif
@endforeach
@if ($ogImage)
    <meta property="og:image" content="{{ $ogImage }}">
@endif
<meta name="twitter:card" content="{{ $ogImage ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $seo->title() }}">
<meta name="twitter:description" content="{{ $seo->description() }}">
@if ($ogImage)
    <meta name="twitter:image" content="{{ $ogImage }}">
@endif
@if ($seoSettings->google_verification)
    <meta name="google-site-verification" content="{{ $seoSettings->google_verification }}">
@endif
@if ($seoSettings->bing_verification)
    <meta name="msvalidate.01" content="{{ $seoSettings->bing_verification }}">
@endif
<script type="application/ld+json">{!! app(\App\Services\SchemaBuilder::class)->json() !!}</script>
