<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\SeoManager;
use Illuminate\Contracts\View\View;

class SearchController extends Controller
{
    public function __invoke(SeoManager $seo): View
    {
        $seo->title(__('search.title'))->noindex(true);

        return view('site.pages.search');
    }
}
