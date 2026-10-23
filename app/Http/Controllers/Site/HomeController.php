<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\HomeSectionResolver;
use App\Services\SeoManager;
use App\Support\LocalizedRoute;
use Illuminate\Contracts\View\View;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class HomeController extends Controller
{
    public function __invoke(HomeSectionResolver $resolver, SeoManager $seo): View
    {
        $seo->alternates(collect(LaravelLocalization::getSupportedLanguagesKeys())
            ->mapWithKeys(fn (string $locale) => [$locale => LocalizedRoute::url('home', $locale)])
            ->all());

        return view('site.pages.home', [
            'sections' => $resolver->forLocale(),
        ]);
    }
}
