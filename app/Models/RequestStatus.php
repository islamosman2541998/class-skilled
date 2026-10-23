<?php

namespace App\Models;

use App\Enums\RequestType;
use App\Enums\StatusColor;
use App\Models\Concerns\LogsModelActivity;
use App\Models\Concerns\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Spatie\Translatable\HasTranslations;

/**
 * Workflow status per request type (editable in the dashboard).
 *
 * @property int $id
 * @property RequestType $request_type
 * @property string $key
 * @property array<string, string> $name
 * @property StatusColor $color
 * @property bool $is_default
 * @property bool $is_final
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class RequestStatus extends Model
{
    use HasTranslations, LogsModelActivity, Sortable;

    protected $fillable = ['request_type', 'key', 'name', 'color', 'is_default', 'is_final', 'sort'];

    /** @var list<string> */
    public array $translatable = ['name'];

    protected function casts(): array
    {
        return [
            'request_type' => RequestType::class,
            'color' => StatusColor::class,
            'is_default' => 'boolean',
            'is_final' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (RequestStatus $status): void {
            if ($status->is_default) {
                static::query()
                    ->where('request_type', $status->request_type->value)
                    ->whereKeyNot($status->getKey())
                    ->update(['is_default' => false]);
            }
        });
    }

    /**
     * @param  Builder<RequestStatus>  $query
     */
    public function scopeFor(Builder $query, RequestType $type): void
    {
        $query->where('request_type', $type->value)->orderBy('sort');
    }

    public static function defaultFor(RequestType $type): ?self
    {
        return static::query()->where('request_type', $type->value)->orderByDesc('is_default')->orderBy('sort')->first();
    }
}
