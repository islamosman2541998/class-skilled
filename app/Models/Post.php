<?php

namespace App\Models;

use App\Models\Concerns\ClearsSiteCache;
use App\Models\Concerns\HasAssets;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\HasTranslatedSlug;
use App\Models\Concerns\LogsModelActivity;
use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Translatable\HasTranslations;

/**
 * Asset collections: cover.
 *
 * @property int $id
 * @property array<string, string> $title
 * @property array<string, string> $slug
 * @property array<string, string>|null $excerpt
 * @property array<string, string>|null $body
 * @property int|null $post_category_id
 * @property int|null $author_id
 * @property int $reading_minutes
 * @property bool $is_featured
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read PostCategory|null $category
 * @property-read User|null $author
 */
class Post extends Model
{
    use ClearsSiteCache, HasAssets, HasSeo, HasTranslatedSlug, HasTranslations, LogsModelActivity, Publishable;

    protected $fillable = [
        'title', 'slug', 'excerpt', 'body', 'post_category_id', 'author_id', 'is_featured', 'status', 'published_at',
    ];

    /** @var list<string> */
    public array $translatable = ['title', 'slug', 'excerpt', 'body'];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'reading_minutes' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Post $post): void {
            if ($post->isDirty('body')) {
                $body = [];

                foreach ($post->getTranslations('body') as $locale => $html) {
                    $body[$locale] = trim(strip_tags((string) $html)) === '' ? '' : (string) clean((string) $html, 'rich');
                }

                $post->setTranslations('body', array_filter($body, fn (string $value) => $value !== ''));
            }

            $text = trim(strip_tags((string) $post->getTranslation('body', 'ar', false)));
            $words = $text === '' ? 0 : count(preg_split('/\s+/u', $text) ?: []);
            $post->reading_minutes = max(1, (int) ceil(max($words, 1) / 200));
        });
    }

    public function publicRouteName(): string
    {
        return 'posts.show';
    }

    /**
     * @return BelongsTo<PostCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'post_category_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
