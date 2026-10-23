<?php

return [
    'supportedLocales' => [
        'ar' => ['name' => 'Arabic', 'script' => 'Arab', 'native' => 'العربية', 'regional' => 'ar_AE', 'dir' => 'rtl'],
        'en' => ['name' => 'English', 'script' => 'Latn', 'native' => 'English', 'regional' => 'en_US', 'dir' => 'ltr'],
    ],
    'useAcceptLanguageHeader' => false,
    'hideDefaultLocaleInURL' => true,
    'localesOrder' => ['ar', 'en'],
    'localesMapping' => [],
    'utf8suffix' => env('LARAVELLOCALIZATION_UTF8SUFFIX', PHP_OS_FAMILY === 'Windows' ? '' : '.UTF-8'),
    'urlsIgnored' => ['/'.env('ADMIN_PATH', 'cs-panel'), '/'.env('ADMIN_PATH', 'cs-panel').'/*', '/t', '/sitemap.xml'],
    'httpMethodsIgnored' => ['POST', 'PUT', 'PATCH', 'DELETE'],
];
