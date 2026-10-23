{{-- Self-contained: must render even when the database or cache is unavailable. --}}
@php
    $locale = app()->getLocale();
    $t = function (string $key, string $ar, string $en) use ($locale) {
        try {
            $value = __($key);

            return $value !== $key ? $value : ($locale === 'ar' ? $ar : $en);
        } catch (\Throwable) {
            return $locale === 'ar' ? $ar : $en;
        }
    };
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $t('errors.500_title', 'حدث خطأ في الخادم', 'Server error') }}</title>
    <style>
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; background: #1B2A47; color: #C9D3E3; font-family: system-ui, 'Segoe UI', Tahoma, sans-serif; text-align: center; padding: 24px; }
        h1 { color: #fff; font-size: 24px; margin: 24px 0 8px; }
        p { max-width: 46ch; margin: 0 auto; line-height: 1.8; }
        a { display: inline-block; margin-top: 24px; padding: 12px 24px; border-radius: 9999px; background: #4FC3EE; color: #1B2A47; font-weight: 700; text-decoration: none; }
        img { height: 72px; width: auto; }
    </style>
</head>
<body>
    <main>
        <img src="{{ asset('images/brand/mark-white.png') }}" alt="" width="105" height="72">
        <h1>{{ $t('errors.500_title', 'حدث خطأ في الخادم', 'Server error') }}</h1>
        <p>{{ $t('errors.500_text', 'نعتذر، حدث خطأ غير متوقع. حاول مرة أخرى بعد قليل.', 'Sorry, something went wrong. Please try again shortly.') }}</p>
        <a href="{{ url('/') }}">{{ $t('site.back_home', 'العودة إلى الرئيسية', 'Back to home') }}</a>
    </main>
</body>
</html>
