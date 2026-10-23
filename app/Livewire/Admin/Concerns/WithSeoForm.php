<?php

namespace App\Livewire\Admin\Concerns;

use App\Models\ContentModel;
use App\Models\SeoMeta;
use App\Settings\SeoSettings;
use App\Support\Translate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Str;

/**
 * SEO side panel of content editors (`$seo` property): title (60), description
 * (160), OG image, index/noindex, canonical; Google snippet preview per locale.
 */
trait WithSeoForm
{
    /** @var array{title: array{ar: string, en: string}, description: array{ar: string, en: string}, canonical: array{ar: string, en: string}, og_asset_id: int|null, robots: string} */
    public array $seo = [
        'title' => ['ar' => '', 'en' => ''],
        'description' => ['ar' => '', 'en' => ''],
        'canonical' => ['ar' => '', 'en' => ''],
        'og_asset_id' => null,
        'robots' => 'index',
    ];

    protected function loadSeo(?ContentModel $model): void
    {
        /** @var SeoMeta|null $meta */
        $meta = $model?->getRelationValue('seo');

        $this->seo = [
            'title' => Translate::pair($meta?->title),
            'description' => Translate::pair($meta?->description),
            'canonical' => Translate::pair($meta?->canonical),
            'og_asset_id' => $meta?->og_asset_id,
            'robots' => $meta->robots ?? 'index',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function seoRules(): array
    {
        return [
            'seo.title.ar' => ['nullable', 'string', 'max:70'],
            'seo.title.en' => ['nullable', 'string', 'max:70'],
            'seo.description.ar' => ['nullable', 'string', 'max:200'],
            'seo.description.en' => ['nullable', 'string', 'max:200'],
            'seo.canonical.ar' => ['nullable', 'url', 'max:500'],
            'seo.canonical.en' => ['nullable', 'url', 'max:500'],
            'seo.og_asset_id' => ['nullable', 'integer', 'exists:assets,id'],
            'seo.robots' => ['required', 'in:index,noindex'],
        ];
    }

    protected function saveSeo(ContentModel $model): void
    {
        $values = [
            'title' => array_filter(Translate::pair($this->seo['title'])),
            'description' => array_filter(Translate::pair($this->seo['description'])),
            'canonical' => array_filter(Translate::pair($this->seo['canonical'])),
            'og_asset_id' => $this->seo['og_asset_id'] ? (int) $this->seo['og_asset_id'] : null,
            'robots' => $this->seo['robots'] === 'noindex' ? 'noindex' : 'index',
        ];

        $empty = $values['title'] === [] && $values['description'] === [] && $values['canonical'] === []
            && $values['og_asset_id'] === null && $values['robots'] === 'index';

        /** @var MorphOne<SeoMeta, Model> $relation */
        $relation = $model->seo();

        if ($empty) {
            $relation->delete();

            return;
        }

        $relation->updateOrCreate([], $values);
    }

    /**
     * Google-like snippet for the side panel.
     *
     * @return array{title: string, description: string, url: string}
     */
    public function snippet(string $locale, string $fallbackTitle, string $fallbackDescription, ?string $url): array
    {
        $suffix = Translate::pick(app(SeoSettings::class)->title_suffix, $locale);
        $title = filled($this->seo['title'][$locale] ?? null) ? $this->seo['title'][$locale] : $fallbackTitle;
        $description = filled($this->seo['description'][$locale] ?? null) ? $this->seo['description'][$locale] : $fallbackDescription;

        if (blank($description)) {
            $description = Translate::pick(app(SeoSettings::class)->default_description, $locale);
        }

        return [
            'title' => Str::limit(trim($title.($suffix ? ' | '.$suffix : '')), 70),
            'description' => Str::limit(trim(strip_tags($description)), 160),
            'url' => rawurldecode((string) $url),
        ];
    }
}
