<?php

namespace App\Models;

use App\Enums\SiteEventType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property SiteEventType $type
 * @property string|null $path
 * @property string|null $locale
 * @property string|null $device
 * @property array<string, string>|null $utm
 * @property Carbon|null $created_at
 */
class SiteEvent extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['type', 'path', 'locale', 'device', 'utm'];

    protected function casts(): array
    {
        return [
            'type' => SiteEventType::class,
            'utm' => 'array',
        ];
    }
}
