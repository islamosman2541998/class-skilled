<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use App\Services\SiteCache;
use App\Support\LocalizedRoute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $from_path
 * @property string $to_path
 * @property int $status_code
 * @property int $hits
 * @property bool $is_auto
 * @property Carbon|null $last_hit_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Redirect extends Model
{
    use LogsModelActivity;

    public const CACHE_KEY = 'redirects.map';

    protected $fillable = ['from_path', 'to_path', 'status_code', 'is_auto'];

    protected function casts(): array
    {
        return [
            'is_auto' => 'boolean',
            'last_hit_at' => 'datetime',
            'status_code' => 'integer',
            'hits' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Redirect $redirect): void {
            $redirect->from_path = LocalizedRoute::normalizePath($redirect->from_path);

            if (! preg_match('#^https?://#i', $redirect->to_path)) {
                $redirect->to_path = LocalizedRoute::normalizePath($redirect->to_path);
            }
        });

        $flush = fn () => app(SiteCache::class)->forget(self::CACHE_KEY);
        static::saved($flush);
        static::deleted($flush);
    }

    /**
     * Automatic redirect after a slug change, without chains or loops:
     * redirects that pointed to the old path are re-pointed to the new one and
     * any redirect starting at the new path is removed.
     */
    public static function createAuto(string $from, string $to): ?self
    {
        $from = LocalizedRoute::normalizePath($from);
        $to = LocalizedRoute::normalizePath($to);

        if ($from === $to) {
            return null;
        }

        static::query()->where('from_path', $to)->get()->each->delete();

        static::query()->where('to_path', $from)->get()->each(function (Redirect $redirect) use ($to): void {
            if ($redirect->from_path === $to) {
                $redirect->delete();

                return;
            }

            $redirect->update(['to_path' => $to]);
        });

        return static::query()->updateOrCreate(
            ['from_path' => $from],
            ['to_path' => $to, 'status_code' => 301, 'is_auto' => true],
        );
    }

    /**
     * Would saving from → to create a loop through existing redirects?
     */
    public static function wouldLoop(string $from, string $to, ?int $ignoreId = null): bool
    {
        $from = LocalizedRoute::normalizePath($from);
        $current = LocalizedRoute::normalizePath($to);
        $map = static::query()->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->pluck('to_path', 'from_path')->all();

        for ($i = 0; $i < 20; $i++) {
            if ($current === $from) {
                return true;
            }

            if (! isset($map[$current])) {
                return false;
            }

            $current = $map[$current];
        }

        return true;
    }
}
