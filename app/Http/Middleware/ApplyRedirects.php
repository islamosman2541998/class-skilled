<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use App\Services\SiteCache;
use App\Support\LocalizedRoute;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies dashboard redirects before routing (GET/HEAD only), using a cached
 * from → [to, code] map. Arabic paths are compared percent-decoded.
 */
class ApplyRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
            return $next($request);
        }

        $path = LocalizedRoute::normalizePath('/'.ltrim(rawurldecode($request->getPathInfo()), '/'));
        $map = app(SiteCache::class)->remember(Redirect::CACHE_KEY, fn () => Redirect::query()
            ->get(['from_path', 'to_path', 'status_code'])
            ->mapWithKeys(fn (Redirect $r) => [$r->from_path => [$r->to_path, $r->status_code]])
            ->all());

        if (! isset($map[$path])) {
            return $next($request);
        }

        [$to, $code] = $map[$path];

        DB::table('redirects')->where('from_path', $path)->update([
            'hits' => DB::raw('hits + 1'),
            'last_hit_at' => now(),
        ]);

        // Location headers must be ASCII: percent-encode each path segment (Arabic slugs).
        $target = preg_match('#^https?://#i', $to) ? $to : url(implode('/', array_map('rawurlencode', explode('/', $to))));
        $query = $request->getQueryString();

        return redirect()->to($target.($query ? '?'.$query : ''), in_array($code, [301, 302], true) ? $code : 301);
    }
}
