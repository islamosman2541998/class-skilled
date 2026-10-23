<?php

namespace App\Livewire\Site;

use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Service;
use App\Support\Search;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Projects listing: category tabs with counts, service filter, sort, search and
 * "load more" (9 per step). Filters live in the query string so URLs are shareable.
 *
 * @property-read Collection<int, ProjectCategory> $categories
 * @property-read array<int, int> $counts
 * @property-read Collection<int, Service> $services
 */
class ProjectsIndex extends Component
{
    public const STEP = 9;

    #[Url(as: 'cat', except: null)]
    public ?int $category = null;

    #[Url(except: null)]
    public ?int $service = null;

    #[Url(except: 'newest')]
    public string $sort = 'newest';

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public int $limit = self::STEP;

    public function mount(?int $categoryId = null): void
    {
        $this->category ??= $categoryId;
        $this->sort = in_array($this->sort, ['newest', 'oldest', 'featured'], true) ? $this->sort : 'newest';
    }

    public function setCategory(?int $id): void
    {
        $this->category = $id;
        $this->limit = self::STEP;
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['service', 'sort', 'search'], true)) {
            $this->limit = self::STEP;
        }
    }

    public function loadMore(): void
    {
        $this->limit += self::STEP;
    }

    /**
     * @return Builder<Project>
     */
    private function base(bool $withCategory = true): Builder
    {
        $locale = app()->getLocale();
        $search = trim(mb_substr($this->search, 0, 80));

        return Project::query()->published()->translated()
            ->when($withCategory && $this->category, fn (Builder $q) => $q->where('category_id', $this->category))
            ->when($this->service, fn (Builder $q) => $q->whereHas('services', fn (Builder $s) => $s->where('services.id', $this->service)))
            ->when($search !== '', fn (Builder $q) => $q->where(fn (Builder $w) => $w
                ->where('title->'.$locale, 'like', Search::like($search))
                ->orWhere('excerpt->'.$locale, 'like', Search::like($search))
                ->orWhere('location->'.$locale, 'like', Search::like($search))));
    }

    #[Computed]
    public function categories(): Collection
    {
        return ProjectCategory::query()->active()->translated()->ordered()->get();
    }

    /**
     * Number of matching projects per category (other filters applied).
     *
     * @return array<int, int>
     */
    #[Computed]
    public function counts(): array
    {
        return $this->base(false)->toBase()
            ->selectRaw('category_id, count(*) as aggregate')
            ->groupBy('category_id')
            ->pluck('aggregate', 'category_id')
            ->map(fn ($count) => (int) $count)
            ->all();
    }

    #[Computed]
    public function services(): Collection
    {
        return Service::query()->active()->translated()->ordered()->whereHas('projects')->get(['id', 'title', 'slug']);
    }

    public function render(): View
    {
        $query = $this->base()->with(['category', 'assets.media']);

        match ($this->sort) {
            'oldest' => $query->oldest('published_at'),
            'featured' => $query->orderByDesc('is_featured')->latest('published_at'),
            default => $query->latest('published_at'),
        };

        $projects = $query->limit($this->limit + 1)->get();

        return view('livewire.site.projects-index', [
            'projects' => $projects->take($this->limit),
            'hasMore' => $projects->count() > $this->limit,
            'total' => array_sum($this->counts),
        ]);
    }
}
