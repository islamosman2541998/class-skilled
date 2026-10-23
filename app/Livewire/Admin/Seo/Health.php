<?php

namespace App\Livewire\Admin\Seo;

use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Support\AdminLinks;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Content missing SEO title / description / OG image or English translation.
 */
#[Layout('layouts.admin')]
class Health extends Component
{
    public const ISSUES = ['seo_title', 'seo_description', 'og_image', 'english'];

    /** @var array<string, class-string<Model>> */
    public const TYPES = ['page' => Page::class, 'service' => Service::class, 'project' => Project::class, 'post' => Post::class];

    #[Url(as: 'issue', except: '')]
    public string $issue = '';

    #[Url(as: 'type', except: '')]
    public string $type = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('manage seo'), 403);
    }

    /**
     * @return list<array{type: string, title: string, url: string|null, issues: list<string>}>
     */
    #[Computed]
    public function rows(): array
    {
        $rows = [];

        foreach (self::TYPES as $type => $class) {
            if ($this->type !== '' && $this->type !== $type) {
                continue;
            }

            foreach ($class::query()->with(['seo', 'assets.media'])->get() as $model) {
                $issues = $this->issuesFor($model);

                if ($this->issue !== '') {
                    $issues = in_array($this->issue, $issues, true) ? $issues : [];
                }

                if ($issues !== []) {
                    $rows[] = [
                        'type' => $type,
                        'title' => AdminLinks::title($model),
                        'url' => AdminLinks::editUrl($type, $model->getKey()),
                        'issues' => $issues,
                    ];
                }
            }
        }

        return $rows;
    }

    /**
     * @return list<string>
     */
    private function issuesFor(Page|Service|Project|Post $model): array
    {
        $seo = $model->getRelationValue('seo');
        $issues = [];

        if (blank($seo?->getTranslation('title', 'ar', false))) {
            $issues[] = 'seo_title';
        }

        if (blank($seo?->getTranslation('description', 'ar', false))) {
            $issues[] = 'seo_description';
        }

        if ($seo?->og_asset_id === null && $model->firstAsset('cover') === null) {
            $issues[] = 'og_image';
        }

        $field = in_array('title', $model->getTranslatableAttributes(), true) ? 'title' : 'name';

        if (blank($model->getTranslation($field, 'en', false))) {
            $issues[] = 'english';
        }

        return $issues;
    }

    public function render(): View
    {
        return view('livewire.admin.seo.health')->title(__('admin.nav.seo_health'));
    }
}
