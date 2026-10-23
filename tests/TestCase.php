<?php

namespace Tests;

use App\Support\LinkResolver;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Mcamara\LaravelLocalization\LaravelLocalization;

abstract class TestCase extends BaseTestCase
{
    protected $seed = true;

    protected function refreshApplicationWithLocale(string $locale, bool $prefixed = false): void
    {
        $this->tearDown();
        // The hidden default locale has no routing prefix, just like a real request to /.
        $prefix = $locale === 'ar' && ! $prefixed ? '' : $locale;
        putenv(LaravelLocalization::ENV_ROUTE_KEY.'='.$prefix);
        $this->setUp();
    }

    protected function setUp(): void
    {
        parent::setUp();
        LinkResolver::forgetMemo();
    }

    protected function tearDown(): void
    {
        putenv(LaravelLocalization::ENV_ROUTE_KEY);
        parent::tearDown();
    }
}
