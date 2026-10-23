<?php

namespace App\Models;

use App\Models\Concerns\ClearsSiteCache;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\HasTranslatedSlug;
use App\Models\Concerns\LogsModelActivity;
use App\Models\Concerns\Sortable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property array<string, string> $name
 * @property array<string, string> $slug
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Post> $posts
 */
class PostCategory extends Model
{
    use ClearsSiteCache, HasSeo, HasTranslatedSlug, HasTranslations, LogsModelActivity, Sortable;

    protected $fillable = ['name', 'slug', 'sort'];

    /** @var list<string> */
    public array $translatable = ['name', 'slug'];

    public function publicRouteName(): string
    {
        return 'posts.category';
    }

    /**
     * @return HasMany<Post, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    protected function seoDescriptionFields(): array
    {
        return [];
    }
}
