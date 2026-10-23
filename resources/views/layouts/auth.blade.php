@php($locale = app()->getLocale())
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title ?? __('admin.auth.login_title') }} — {{ __('admin.panel_name') }}</title>
    <link rel="icon" href="{{ asset('images/brand/mark.png') }}">
    @vite(['resources/css/admin.css', 'resources/js/admin/app.js'])
    @livewireStyles
</head>
<body class="admin bg-white">
    <div class="grid min-h-screen lg:grid-cols-[minmax(0,1fr)_minmax(0,1.15fr)]">
        <main class="flex flex-col px-6 py-8 sm:px-12">
            <div class="flex justify-end">
                <form method="POST" action="{{ route('admin.locale', $locale === 'ar' ? 'en' : 'ar') }}">
                    @csrf
                    <button type="submit" class="text-[13.5px] font-semibold underline underline-offset-4" lang="{{ $locale === 'ar' ? 'en' : 'ar' }}">{{ __('admin.topbar.switch_language') }}</button>
                </form>
            </div>
            <div class="mx-auto flex w-full max-w-[480px] flex-1 flex-col justify-center py-10">
                <img src="{{ asset('images/brand/mark.png') }}" alt="{{ __('admin.brand') }}" class="mb-8 h-14 w-14 object-contain">
                {{ $slot }}
            </div>
        </main>
        <div class="relative hidden lg:block">
            <img src="{{ asset('images/admin/login.webp') }}" alt="" class="absolute inset-0 h-full w-full object-cover">
            <div class="absolute bottom-10 start-10 max-w-sm rounded-2xl bg-[rgb(27_42_71/.82)] px-6 py-4 text-white backdrop-blur-md">
                <p class="font-[family-name:var(--font-head)] text-[17px] font-extrabold">{{ __('admin.auth.photo_caption_title') }}</p>
                <p class="mt-1 text-[13.5px] text-white/80">{{ __('admin.auth.photo_caption_text') }}</p>
            </div>
        </div>
    </div>
    @livewireScriptConfig
</body>
</html>
