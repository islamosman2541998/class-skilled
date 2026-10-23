<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\Sitemap;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Serves the sitemap index and the per-type sitemaps written by App\Services\Sitemap
 * (generated on demand when a file is missing).
 */
class SitemapController extends Controller
{
    public function __invoke(Sitemap $sitemap, ?string $type = null): BinaryFileResponse
    {
        abort_if($type !== null && ! in_array($type, Sitemap::TYPES, true), 404);

        $path = $sitemap->path($type);

        if (! is_file($path)) {
            $sitemap->regenerate();
        }

        abort_unless(is_file($path), 404);

        return response()->file($path, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
            'X-Robots-Tag' => 'noindex',
        ]);
    }
}
