<?php

namespace App\Models;

use App\Models\Concerns\ClearsSiteCache;
use App\Models\Concerns\LogsModelActivity;
use App\Models\Concerns\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Spatie\Translatable\HasTranslations;

/**
 * General FAQ (faqable null) or attached to a service.
 *
 * @property int $id
 * @property array<string, string> $question
 * @property array<string, string> $answer
 * @property string|null $faqable_type
 * @property int|null $faqable_id
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Model|null $faqable
 */
class Faq extends ContentModel
{
    use ClearsSiteCache, HasTranslations, LogsModelActivity, Sortable;

    protected $fillable = ['question', 'answer', 'faqable_type', 'faqable_id', 'is_active', 'sort'];

    /** @var list<string> */
    public array $translatable = ['question', 'answer'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function faqable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @param  Builder<Faq>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<Faq>  $query
     */
    public function scopeGeneral(Builder $query): void
    {
        $query->whereNull('faqable_type');
    }
}
