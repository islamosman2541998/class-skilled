<?php

namespace App\Http\Controllers\Site;

use App\Enums\HeroMediaType;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\Concerns\ResolvesPublicModels;
use App\Models\Asset;
use App\Models\Faq;
use App\Models\Page;
use App\Services\SeoManager;
use App\Support\Blocks\Blocks;
use App\Support\Translate;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

class PageController extends Controller
{
    use ResolvesPublicModels;

    public function show(string $slug): View
    {
        /** @var Page $page */
        $page = $this->findPublic(Page::query()->with(['assets.media', 'assets.poster.media', 'parent', 'seo']), $slug);

        return view('site.pages.page', [
            'page' => $page,
            'preview' => $this->preview,
            'hero' => $this->hero($page),
            'blocks' => Blocks::prepare($page->blocks),
            'attachments' => $this->attachments($page),
        ]);
    }

    public function faq(SeoManager $seo): View
    {
        $seo->title(__('faq.title'))->description(__('faq.lead'));

        $faqs = Faq::query()->active()->general()->ordered()->get()
            ->filter(fn (Faq $faq) => filled($faq->getTranslation('question', app()->getLocale(), false)))
            ->values();

        return view('site.pages.faq', ['faqs' => $faqs]);
    }

    /**
     * Main media of the page: `{type, image?, mobile?, video?, embed?, pdf?}` or null.
     *
     * @return array<string, mixed>|null
     */
    private function hero(Page $page): ?array
    {
        $type = $page->hero_type;

        $hero = match ($type) {
            HeroMediaType::Image => ['image' => $page->firstAsset('hero_image'), 'mobile' => $page->firstAsset('hero_mobile_image')],
            HeroMediaType::Video => ['video' => $page->firstAsset('hero_video')],
            HeroMediaType::Embed => ['embed' => $page->firstAsset('hero_video')],
            HeroMediaType::Pdf => ['pdf' => $page->firstAsset('hero_pdf')],
            HeroMediaType::None => null,
        };

        if ($hero === null || array_filter($hero) === []) {
            return null;
        }

        return ['type' => $type->value] + $hero;
    }

    /**
     * Visible attachment groups in display order (files → videos → images).
     *
     * @return list<array{group: string, title: string, display: string, columns: int, assets: Collection<int, Asset>}>
     */
    private function attachments(Page $page): array
    {
        $groups = [];
        $config = $page->attachments_config ?? [];

        foreach (Page::ATTACHMENT_GROUPS as $group) {
            $settings = $config[$group] ?? [];
            $assets = $page->assetsIn('att_'.$group);

            if (! ($settings['visible'] ?? true) || $assets->isEmpty()) {
                continue;
            }

            $groups[] = [
                'group' => $group,
                'title' => Translate::pick($settings['title'] ?? []),
                'display' => (string) ($settings['display'] ?? ($group === 'files' ? 'list' : 'grid')),
                'columns' => (int) ($settings['columns'] ?? 3),
                'assets' => $assets,
            ];
        }

        return $groups;
    }
}
