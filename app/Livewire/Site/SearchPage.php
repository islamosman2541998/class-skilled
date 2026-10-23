<?php

namespace App\Livewire\Site;

use App\Models\ContentModel;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Support\Search;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Site search across published services, projects, articles and pages
 * (title + excerpt in the current locale), grouped with counts. Min 2 chars,
 * rate-limited per visitor.
 */
class SearchPage extends Component
{
    public const LIMIT = 12;

    #[Url(as: 'q', except: '')]
    public string $query = '';

    public bool $throttled = false;

    /**
     * @return array<string, array{label: string, items: list<array{title: string, url: string, excerpt: string}>}>
     */
    private function results(string $term): array
    {
        $locale = app()->getLocale();
        $like = Search::like($term);
        $match = fn (Builder $q) => $q->where(fn (Builder $w) => $w
            ->where('title->'.$locale, 'like', $like)
            ->orWhere('excerpt->'.$locale, 'like', $like));

        $groups = [
            'services' => [__('search.group_services'), Service::query()->active()->translated()],
            'projects' => [__('search.group_projects'), Project::query()->published()->translated()],
            'posts' => [__('search.group_posts'), Post::query()->published()->translated()],
            'pages' => [__('search.group_pages'), Page::query()->published()->translated()],
        ];

        $results = [];

        foreach ($groups as $key => [$label, $query]) {
            /** @var Builder<ContentModel> $query */
            $match($query);
            $items = $query->limit(self::LIMIT)->get()
                ->map(fn (ContentModel $model) => [
                    'title' => (string) $model->getTranslation('title', $locale),
                    'url' => (string) $model->localizedUrl($locale),
                    'excerpt' => Str::limit(strip_tags((string) $model->getTranslation('excerpt', $locale, false)), 180),
                ])
                ->filter(fn (array $item) => $item['url'] !== '')
                ->values()->all();

            if ($items !== []) {
                $results[$key] = ['label' => $label, 'items' => $items];
            }
        }

        return $results;
    }

    public function render(): View
    {
        $term = trim(mb_substr($this->query, 0, 80));
        $results = [];
        $this->throttled = false;

        if (mb_strlen($term) >= 2) {
            $key = 'site-search:'.request()->ip();

            if (RateLimiter::tooManyAttempts($key, 30)) {
                $this->throttled = true;
            } else {
                RateLimiter::hit($key, 60);
                $results = $this->results($term);
            }
        }

        return view('livewire.site.search-page', [
            'term' => $term,
            'results' => $results,
            'total' => array_sum(array_map(fn (array $group) => count($group['items']), $results)),
        ]);
    }
}
