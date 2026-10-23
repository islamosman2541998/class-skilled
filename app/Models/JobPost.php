<?php

namespace App\Models;

use App\Enums\JobType;
use App\Models\Concerns\ClearsSiteCache;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\HasTranslatedSlug;
use App\Models\Concerns\LogsModelActivity;
use App\Models\Concerns\Publishable;
use App\Models\Concerns\Sortable;
use App\Support\Translate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property array<string, string> $title
 * @property array<string, string> $slug
 * @property array<string, string>|null $department
 * @property JobType $job_type
 * @property array<string, string>|null $location
 * @property array<string, string>|null $description
 * @property array<string, list<string>>|null $requirements
 * @property Carbon|null $closes_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, JobApplication> $applications
 */
class JobPost extends Model
{
    use ClearsSiteCache, HasSeo, HasTranslatedSlug, HasTranslations, LogsModelActivity, Publishable, Sortable;

    protected $fillable = [
        'title', 'slug', 'department', 'job_type', 'location', 'description', 'requirements',
        'status', 'published_at', 'closes_at', 'sort',
    ];

    /** @var list<string> */
    public array $translatable = ['title', 'slug', 'department', 'location', 'description'];

    protected function casts(): array
    {
        return [
            'job_type' => JobType::class,
            'requirements' => 'array',
            'closes_at' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (JobPost $job): void {
            if ($job->isDirty('description')) {
                $clean = [];

                foreach ($job->getTranslations('description') as $locale => $html) {
                    $clean[$locale] = trim(strip_tags((string) $html)) === '' ? '' : (string) clean((string) $html, 'rich');
                }

                $job->setTranslations('description', array_filter($clean, fn (string $value) => $value !== ''));
            }
        });
    }

    public function publicRouteName(): string
    {
        return 'careers.show';
    }

    /**
     * @return HasMany<JobApplication, $this>
     */
    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    /**
     * Published and not past its closing date.
     *
     * @param  Builder<JobPost>  $query
     */
    public function scopeOpen(Builder $query): void
    {
        $query->published()->where(fn (Builder $q) => $q->whereNull('closes_at')->orWhereDate('closes_at', '>=', today()));
    }

    public function isOpen(): bool
    {
        return $this->isPublished() && ($this->closes_at === null || $this->closes_at->endOfDay()->isFuture());
    }

    /**
     * @return list<string>
     */
    public function requirementsList(?string $locale = null): array
    {
        return Translate::list($this->requirements, $locale);
    }

    protected function seoDescriptionFields(): array
    {
        return ['description'];
    }
}
