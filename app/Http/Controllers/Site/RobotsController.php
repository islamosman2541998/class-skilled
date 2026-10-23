<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Settings\SeoSettings;
use Illuminate\Http\Response;

/**
 * Dynamic robots.txt from SEO settings, always ending with the sitemap URL.
 * Non-production environments disallow everything.
 */
class RobotsController extends Controller
{
    public function __invoke(): Response
    {
        $body = app()->isProduction()
            ? trim(app(SeoSettings::class)->robots_txt)
            : "User-agent: *\nDisallow: /";

        if (! str_contains(strtolower($body), 'user-agent')) {
            $body = "User-agent: *\n".$body;
        }

        $body .= "\n\nSitemap: ".url('/sitemap.xml')."\n";

        return response($body, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
