<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

/**
 * Environment report for shared hosting (artisan `site:check` and Dashboard → System → Health):
 * PHP version and extensions, writable folders, storage link (or fallback),
 * database, cache, mail, and production flags.
 */
class SiteCheck
{
    public const EXTENSIONS = ['gd', 'fileinfo', 'intl', 'mbstring', 'zip', 'pdo_mysql', 'exif', 'openssl', 'json', 'xml', 'curl'];

    /**
     * @return list<array{group: string, label: string, status: string, detail: string}> status: ok | warn | fail
     */
    public function run(): array
    {
        $checks = [];
        $add = function (string $group, string $label, string $status, string $detail = '') use (&$checks): void {
            $checks[] = compact('group', 'label', 'status', 'detail');
        };

        // PHP
        $add('php', 'PHP '.PHP_VERSION, version_compare((string) phpversion(), '8.2.0', '>=') ? 'ok' : 'fail', '>= 8.2');

        foreach (self::EXTENSIONS as $extension) {
            $loaded = extension_loaded($extension);
            $optional = in_array($extension, ['exif', 'intl'], true);
            $add('php', 'ext-'.$extension, $loaded ? 'ok' : ($optional ? 'warn' : 'fail'), $loaded ? '' : 'missing');
        }

        $add('php', 'upload_max_filesize', 'ok', (string) ini_get('upload_max_filesize'));
        $add('php', 'post_max_size', 'ok', (string) ini_get('post_max_size'));

        // Folders
        foreach (['storage/app', 'storage/framework/cache', 'storage/framework/sessions', 'storage/framework/views', 'storage/logs', 'bootstrap/cache'] as $dir) {
            $path = base_path($dir);
            $add('files', $dir, is_dir($path) && is_writable($path) ? 'ok' : 'fail', is_dir($path) ? (is_writable($path) ? 'writable' : 'not writable') : 'missing');
        }

        $link = public_path('storage');
        $linked = is_link($link) || is_dir($link);
        $add('files', 'public/storage', $linked ? 'ok' : 'warn', $linked ? 'linked' : 'no symlink: files are served by the /storage fallback route');
        $add('files', 'build manifest', is_file(public_path('build/manifest.json')) ? 'ok' : 'fail', is_file(public_path('build/manifest.json')) ? '' : 'run npm run build and upload public/build');

        // Services
        try {
            DB::select('select 1');
            $add('services', 'database ('.DB::getDriverName().')', 'ok', (string) DB::getDatabaseName());
        } catch (Throwable $e) {
            $add('services', 'database', 'fail', Str::limit($e->getMessage(), 120));
        }

        try {
            Cache::put('site-check', 'ok', 10);
            $add('services', 'cache ('.config('cache.default').')', Cache::get('site-check') === 'ok' ? 'ok' : 'fail');
        } catch (Throwable $e) {
            $add('services', 'cache', 'fail', Str::limit($e->getMessage(), 120));
        }

        $mailer = (string) config('mail.default');
        $add('services', 'mail ('.$mailer.')', in_array($mailer, ['log', 'array'], true) ? (app()->isProduction() ? 'fail' : 'warn') : 'ok', (string) config('mail.from.address'));
        $add('services', 'queue ('.config('queue.default').')', config('queue.default') === 'sync' ? 'ok' : 'warn', 'shared hosting: sync');

        // App flags
        $add('app', 'APP_ENV', 'ok', (string) app()->environment());
        $add('app', 'APP_DEBUG', app()->isProduction() && config('app.debug') ? 'fail' : 'ok', config('app.debug') ? 'true' : 'false');
        $add('app', 'APP_URL', str_starts_with((string) config('app.url'), 'https://') || ! app()->isProduction() ? 'ok' : 'warn', (string) config('app.url'));
        $add('app', 'APP_KEY', filled(config('app.key')) ? 'ok' : 'fail');
        $add('app', 'config cache', app()->configurationIsCached() ? 'ok' : 'warn', app()->configurationIsCached() ? '' : 'run php artisan optimize');
        $add('app', 'route cache', app()->routesAreCached() ? 'ok' : 'warn', app()->routesAreCached() ? '' : 'run php artisan route:trans:cache');

        return $checks;
    }

    /**
     * @param  list<array{status: string}>  $checks
     */
    public static function failed(array $checks): bool
    {
        return collect($checks)->contains('status', 'fail');
    }
}
