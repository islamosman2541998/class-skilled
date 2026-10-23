<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\Concerns\ResolvesPublicModels;
use App\Models\Service;
use App\Services\SeoManager;
use App\Support\Blocks\Blocks;
use Illuminate\Contracts\View\View;

class ServiceController extends Controller
{
    use ResolvesPublicModels;

    public function index(SeoManager $seo): View
    {
        $seo->title(__('services.title'))->description(__('services.lead'));

        return view('site.pages.services.index', [
            'services' => Service::query()->active()->translated()->ordered()->with('assets.media')->get(),
        ]);
    }

    public function show(SeoManager $seo, string $slug): View
    {
        /** @var Service $service */
        $service = $this->findPublic(Service::query()->with(['assets.media', 'seo']), $slug);

        return view('site.pages.services.show', [
            'service' => $service,
            'preview' => $this->preview,
            'blocks' => Blocks::prepare($service->blocks),
            'faqs' => $service->faqs()->active()->ordered()->get(),
            'others' => Service::query()->active()->translated()->ordered()->get(['id', 'title', 'slug']),
            'projects' => $service->projects()->published()->with(['category', 'assets.media'])
                ->translated()
                ->latest('published_at')->limit(8)->get(),
        ]);
    }
}
