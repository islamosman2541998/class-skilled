@php($appearance = app(\App\Services\Appearance::class))
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $seo->title() }}</title>
<meta name="description" content="{{ $seo->description() }}">
<meta name="theme-color" content="{{ app(\App\Settings\AppearanceSettings::class)->colors['deep'] ?? '#1B2A47' }}">
@foreach (\Mcamara\LaravelLocalization\Facades\LaravelLocalization::getSupportedLanguagesKeys() as $alt)
    <link rel="alternate" hreflang="{{ $alt }}" href="{{ $seo->alternateUrl($alt) }}">
@endforeach
@include('site.partials.favicons')
<script>document.documentElement.classList.replace('no-js', 'js');</script>
@if ($fontUrl = $appearance->preloadFontUrl())
    <link rel="preload" href="{{ $fontUrl }}" as="font" type="font/woff2" crossorigin>
@endif
@stack('preload')
@vite([...$appearance->fontEntries(), 'resources/css/site.css', 'resources/js/site/app.js'])
{!! $appearance->styleTag() !!}
@livewireStyles
@stack('head')
