@php
    $locale = app()->getLocale();
    $overlayHeader = trim($__env->yieldContent('header_overlay')) !== '';
    $hideCta = trim($__env->yieldContent('hide_cta')) !== '';
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}" class="no-js">
<head>
    @include('site.partials.head')
</head>
<body @class(['has-overlay-header' => $overlayHeader, 'has-topbar' => true])>
    @include('site.partials.tracking-body')
    <a href="#main" class="skip-link">{{ __('site.skip_to_content') }}</a>

    @include('site.partials.topbar')
    @include('site.partials.header', ['overlay' => $overlayHeader])

    <main id="main" tabindex="-1" class="outline-none">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    @unless ($hideCta)
        @include('site.partials.cta-band')
    @endunless

    @include('site.partials.footer')
    @include('site.partials.mobile-nav')
    @include('site.partials.bottom-bar')

    @if (session('toast'))
        <div hidden data-cs-toast data-type="{{ session('toast.type', 'success') }}" data-message="{{ session('toast.message') }}"></div>
    @endif

    <script>window.csLang = {{ Js::from([
        'slider' => collect(['previous', 'next', 'go_to', 'slide_of'])->mapWithKeys(fn ($k) => [$k => __('slider.'.$k)]),
        'confirm' => __('site.confirm'),
        'cancel' => __('site.cancel'),
    ]) }};</script>
    @livewireScriptConfig
    @stack('scripts')
</body>
</html>
