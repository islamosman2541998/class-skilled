@php($locale = app()->getLocale())
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ __('errors.maintenance_title') }}</title>
    @vite(['resources/css/site.css'])
    {!! app(\App\Services\Appearance::class)->styleTag() !!}
</head>
<body class="flex min-h-screen items-center justify-center bg-[var(--c-deep)] px-4 text-center text-white">
    <main class="max-w-lg">
        <img src="{{ asset('images/brand/logo-white.png') }}" alt="{{ \App\Support\Translate::pick(app(\App\Settings\GeneralSettings::class)->site_name) }}" class="mx-auto h-20 w-auto">
        <h1 class="mt-8 font-heading text-section font-extrabold">{{ __('errors.maintenance_title') }}</h1>
        <p class="mt-3 text-lead text-white/80">{{ $message }}</p>
    </main>
</body>
</html>
