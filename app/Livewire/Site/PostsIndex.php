<?php

namespace App\Livewire\Site;

use App\Models\Post;
use App\Models\PostCategory;
use App\Support\Search;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Articles listing: category tabs, debounced search, featured post on top of
 * the unfiltered first page, paginated grid without reloads.
 *
 * @property-read Collection<int, PostCategory> $categories
 */
class PostsIndex extends Component
{
    use WithPagination;

    public const PER_PAGE = 9;

    #[Url(as: 'cat', except: null)]
    public ?int $category = null;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public function mount(?int $categoryId = null): void
    {
        $this->category ??= $categoryId;
    }

    public function setCategory(?int $id): void
    {
        $this->category = $id;
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function categories(): Collection
    {
        return PostCategory::query()->translated()->ordered()
            ->whereHas('posts', fn (Builder $q) => $q->published())
            ->get();
    }

    public function render(): View
    {
        $locale = app()->getLocale();
        $search = trim(mb_substr($this->search, 0, 80));
        $filtered = $this->category !== null || $search !== '';

        $base = Post::query()->published()->translated()
            ->when($this->category, fn (Builder $q) => $q->where('post_category_id', $this->category))
            ->when($search !== '', fn (Builder $q) => $q->where(fn (Builder $w) => $w
                ->where('title->'.$locale, 'like', Search::like($search))
                ->orWhere('excerpt->'.$locale, 'like', Search::like($search))));

        // The featured post leads the unfiltered list (page 1) and is left out of the grid on every page.
        $featured = ! $filtered
            ? (clone $base)->where('is_featured', true)->with(['category', 'assets.media'])->latest('published_at')->first()
            : null;

        $posts = $base->with(['category', 'assets.media'])
            ->when($featured, fn (Builder $q) => $q->whereKeyNot($featured->id))
            ->latest('published_at')
            ->paginate(self::PER_PAGE);

        return view('livewire.site.posts-index', ['posts' => $posts, 'featured' => $posts->currentPage() === 1 ? $featured : null]);
    }
}
