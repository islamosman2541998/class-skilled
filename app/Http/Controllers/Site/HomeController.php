<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\HomeSectionResolver;
use App\Services\SeoManager;
use App\Settings\SeoSettings;
use App\Support\LocalizedRoute;
use App\Support\Translate;
use Illuminate\Contracts\View\View;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class HomeController extends Controller
{
    public function __invoke(HomeSectionResolver $resolver, SeoManager $seo): View
    {
        $settings = app(SeoSettings::class);

        if (($title = Translate::pick($settings->home_title)) !== '') {
            $seo->fullTitle($title);
        }

        if (($description = Translate::pick($settings->home_description)) !== '') {
            $seo->description($description);
        }

        $seo->alternates(collect(LaravelLocalization::getSupportedLanguagesKeys())
            ->mapWithKeys(fn (string $locale) => [$locale => LocalizedRoute::url('home', $locale)])
            ->all());

        return view('site.pages.home', [
            'sections' => $resolver->forLocale(),
        ]);
    }
}
