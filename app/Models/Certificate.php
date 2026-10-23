<?php

namespace App\Models;

use App\Models\Concerns\ClearsSiteCache;
use App\Models\Concerns\HasAssets;
use App\Models\Concerns\LogsModelActivity;
use App\Models\Concerns\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Spatie\Translatable\HasTranslations;

/**
 * Asset collections: file (pdf), image.
 *
 * @property int $id
 * @property array<string, string> $name
 * @property string|null $number
 * @property array<string, string>|null $authority
 * @property Carbon|null $issued_at
 * @property Carbon|null $expires_at
 * @property string|null $verify_url
 * @property bool $show_on_site
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Certificate extends ContentModel
{
    use ClearsSiteCache, HasAssets, HasTranslations, LogsModelActivity, Sortable;

    protected $fillable = ['name', 'number', 'authority', 'issued_at', 'expires_at', 'verify_url', 'show_on_site', 'sort'];

    /** @var list<string> */
    public array $translatable = ['name', 'authority'];

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'expires_at' => 'date',
            'show_on_site' => 'boolean',
        ];
    }

    /**
     * @param  Builder<Certificate>  $query
     */
    public function scopeVisible(Builder $query): void
    {
        $query->where('show_on_site', true);
    }

    /**
     * @param  Builder<Certificate>  $query
     */
    public function scopeExpiringWithin(Builder $query, int $days): void
    {
        $query->whereNotNull('expires_at')->whereDate('expires_at', '<=', now()->addDays($days));
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->endOfDay()->isPast();
    }

    public function expiresSoon(int $days = 90): bool
    {
        return $this->expires_at !== null && ! $this->isExpired() && $this->expires_at->lte(now()->addDays($days));
    }
}
