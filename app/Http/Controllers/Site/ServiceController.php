<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\Concerns\ResolvesPublicModels;
use App\Models\Service;
use App\Services\SchemaBuilder;
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

    public function show(SeoManager $seo, SchemaBuilder $schema, string $slug): View
    {
        /** @var Service $service */
        $service = $this->findPublic(Service::query()->with(['assets.media', 'seo']), $slug);
        $faqs = $service->faqs()->active()->ordered()->get();
        $locale = app()->getLocale();

        $seo->schema($schema->service($service));
        $faqSchema = $schema->faq($faqs->map(fn ($faq) => ['q' => (string) $faq->getTranslation('question', $locale), 'a' => (string) $faq->getTranslation('answer', $locale)])->all());

        if ($faqSchema !== null) {
            $seo->schema($faqSchema);
        }

        return view('site.pages.services.show', [
            'service' => $service,
            'preview' => $this->preview,
            'blocks' => Blocks::prepare($service->blocks),
            'faqs' => $faqs,
            'others' => Service::query()->active()->translated()->ordered()->get(['id', 'title', 'slug']),
            'projects' => $service->projects()->published()->with(['category', 'assets.media'])
                ->translated()
                ->latest('published_at')->limit(8)->get(),
        ]);
    }
}
