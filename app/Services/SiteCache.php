<?php

namespace App\Services;

use App\Support\LinkResolver;
use Closure;
use Illuminate\Support\Facades\Cache;
use Spatie\ResponseCache\Facades\ResponseCache;

/**
 * Versioned cache for everything the public site reads (menus, home sections,
 * redirects map, appearance CSS…). `flush()` bumps the version, so every key
 * is invalidated at once, and clears the full-page response cache.
 */
class SiteCache
{
    private const VERSION_KEY = 'site_cache_version';

    private ?int $version = null;

    private bool $paused = false;

    /**
     * @template T
     *
     * @param  Closure(): T  $callback
     * @return T
     */
    public function remember(string $key, Closure $callback, int $ttl = 86400): mixed
    {
        return Cache::remember($this->key($key), $ttl, $callback);
    }

    public function forget(string $key): void
    {
        Cache::forget($this->key($key));
    }

    /**
     * Run bulk writes (seeders, imports) with a single flush at the end.
     *
     * @template T
     *
     * @param  Closure(): T  $callback
     * @return T
     */
    public function withoutFlushing(Closure $callback): mixed
    {
        $this->paused = true;

        try {
            return $callback();
        } finally {
            $this->paused = false;
            $this->flush();
        }
    }

    public function flush(): void
    {
        if ($this->paused) {
            return;
        }

        $this->version = $this->version() + 1;
        Cache::forever(self::VERSION_KEY, $this->version);
        LinkResolver::forgetMemo();

        if (config('responsecache.enabled')) {
            ResponseCache::clear();
        }
    }

    public function key(string $key): string
    {
        return 'site.v'.$this->version().'.'.$key;
    }

    private function version(): int
    {
        return $this->version ??= (int) Cache::get(self::VERSION_KEY, 1);
    }
}
