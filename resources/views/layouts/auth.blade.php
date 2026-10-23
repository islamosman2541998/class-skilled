<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('setup.title'))</title>
    @vite(['resources/css/admin.css', 'resources/js/admin/app.js'])
    @livewireStyles
    @stack('head')
</head>
<body>
    <main class="mx-auto max-w-site px-gutter py-10">
        {{ $slot ?? '' }}
        @yield('content')
    </main>
    @livewireScripts
    @stack('scripts')
</body>
</html>
