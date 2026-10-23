<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\Concerns\ResolvesPublicModels;
use App\Models\Asset;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Services\SchemaBuilder;
use App\Services\SeoManager;
use App\Support\Blocks\Blocks;
use Illuminate\Contracts\View\View;

class ProjectController extends Controller
{
    use ResolvesPublicModels;

    public function index(SeoManager $seo): View
    {
        $seo->title(__('projects.title'))->description(__('projects.lead'));

        return view('site.pages.projects.index', ['category' => null]);
    }

    public function category(string $slug): View
    {
        /** @var ProjectCategory $category */
        $category = $this->findPublic(ProjectCategory::query()->with('seo'), $slug);

        return view('site.pages.projects.index', ['category' => $category]);
    }

    public function show(string $slug): View
    {
        /** @var Project $project */
        $project = $this->findPublic(Project::query()->with(['category', 'services', 'assets.media', 'assets.poster.media', 'seo']), $slug);

        $serviceIds = $project->services->pluck('id');
        $related = Project::query()->published()->translated()
            ->whereKeyNot($project->id)
            ->where(fn ($q) => $q->when($project->category_id, fn ($q) => $q->where('category_id', $project->category_id))
                ->orWhereHas('services', fn ($q) => $q->whereIn('services.id', $serviceIds)))
            ->with(['category', 'assets.media'])
            ->latest('published_at')->limit(8)->get();

        $gallery = collect([$project->firstAsset('cover')])->concat($project->assetsIn('gallery'))->filter()->unique('id')->values();
        app(SeoManager::class)->schema(app(SchemaBuilder::class)->project($project, $gallery));

        return view('site.pages.projects.show', [
            'project' => $project,
            'preview' => $this->preview,
            'blocks' => Blocks::prepare($project->blocks),
            'gallery' => $gallery,
            'pairs' => $this->beforeAfterPairs($project),
            'videos' => $project->assetsIn('videos'),
            'related' => $related,
        ]);
    }

    /**
     * Before/after images grouped by their `pair` meta: `[['before' => Asset, 'after' => Asset]]`.
     *
     * @return list<array{before: Asset, after: Asset}>
     */
    private function beforeAfterPairs(Project $project): array
    {
        $pairs = [];

        foreach ($project->assetsIn('before_after') as $asset) {
            $meta = json_decode((string) ($asset->getRelationValue('pivot')->meta ?? '{}'), true) ?: [];
            $pairs[(string) ($meta['pair'] ?? 0)][($meta['side'] ?? 'before') === 'after' ? 'after' : 'before'] = $asset;
        }

        return array_values(array_filter($pairs, fn (array $pair) => isset($pair['before'], $pair['after'])));
    }
}
