<?php

namespace App\Http\CacheProfiles;

use App\Support\Preview;
use Illuminate\Http\Request;
use Spatie\ResponseCache\CacheProfiles\BaseCacheProfile;
use Symfony\Component\HttpFoundation\Response;

/**
 * Full-page cache for guests on public pages. Not cached: logged-in users,
 * active previews, query strings (filters, search, ?service=), Livewire
 * requests, flashed errors/toasts, and pages whose forms rely on a fresh
 * server timestamp (quote, contact, careers) or are personal (search).
 * Cleared by SiteCache::flush() on every content / settings / menu change.
 */
class SiteCacheProfile extends BaseCacheProfile
{
    /** Route names never served from the cache. */
    public const EXCLUDED_ROUTES = ['search', 'quote', 'contact', 'careers.index', 'careers.show'];

    public function shouldCacheRequest(Request $request): bool
    {
        if (! $request->isMethod('GET') || $request->ajax() || $request->hasHeader('X-Livewire')) {
            return false;
        }

        if ($request->query->count() > 0 || $request->user() !== null) {
            return false;
        }

        if ($request->routeIs(...self::EXCLUDED_ROUTES)) {
            return false;
        }

        $session = $request->hasSession() ? $request->session() : null;

        if ($session !== null && ($session->has('errors') || $session->has('toast') || filled($session->get(Preview::SESSION_KEY)))) {
            return false;
        }

        return true;
    }

    public function shouldCacheResponse(Response $response): bool
    {
        return $response->getStatusCode() === 200 && ! $response->headers->has('X-No-Cache');
    }

    /**
     * Guests share one cache entry per URL (the base profile would suffix the user id).
     */
    public function useCacheNameSuffix(Request $request): string
    {
        return '';
    }
}
