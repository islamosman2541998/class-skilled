<?php

test('public placeholders resolve with their translated route name and direction', function (
    string $locale,
    string $path,
    string $routeName,
) {
    $this->refreshApplicationWithLocale($locale);
    $direction = $locale === 'ar' ? 'rtl' : 'ltr';

    $this->get($path)
        ->assertOk()
        ->assertSee('lang="'.$locale.'" dir="'.$direction.'"', false)
        ->assertSee('<bdi dir="ltr">'.$routeName.'</bdi>', false)
        ->assertSee($locale === 'ar' ? 'إعداد المشروع' : 'Project setup');
})->with('public routes');

test('dashboard uses its configured prefix and redirects guests to the login', function () {
    $this->get('/'.config('admin.path'))->assertRedirect(route('login'));
    $this->get(route('login'))->assertOk()->assertSee('lang="ar" dir="rtl"', false);
    $this->withSession(['admin_locale' => 'en'])->get(route('login'))->assertOk()->assertSee('lang="en" dir="ltr"', false);
    $this->withSession(['admin_locale' => 'unknown'])->get(route('login'))->assertOk()->assertSee('lang="ar" dir="rtl"', false);
});

test('default Arabic is canonical and ignores the browser language', function () {
    $this->withHeader('Accept-Language', 'en')->get('/')->assertOk()->assertSee('lang="ar"', false);
    $this->refreshApplicationWithLocale('ar', prefixed: true);
    $this->get('/ar')->assertRedirect(rtrim(config('app.url'), '/').'/');
});

test('infrastructure endpoints precede the page catch-all', function () {
    $this->get('/sitemap.xml')->assertOk()->assertSee('sitemap');
    $this->post('/t')->assertOk()->assertSee('<bdi dir="ltr">track</bdi>', false);
});

test('reference files are not served through the application', function () {
    $this->get('/_design/PROGRESS.md')->assertNotFound();
    $this->get('/_design/brand/logo.png')->assertNotFound();
});

dataset('public routes', [
    ['ar', '/طلب-عرض-سعر', 'quote'],
    ['ar', '/تواصل-معنا', 'contact'],
    ['en', '/en/request-a-quote', 'quote'],
    ['en', '/en/contact', 'contact'],
]);
