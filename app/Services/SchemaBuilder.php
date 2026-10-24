<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\JobPost;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Settings\CompanySettings;
use App\Settings\ContactSettings;
use App\Settings\SeoSettings;
use App\Support\LocalizedRoute;
use App\Support\Translate;
use Illuminate\Support\Str;

/**
 * JSON-LD for the public site: the company (GeneralContractor) and WebSite on
 * every page, the breadcrumb trail, plus page nodes (Service, Article,
 * FAQPage, JobPosting, project ImageObjects) collected by SeoManager.
 */
class SchemaBuilder
{
    public function __construct(private readonly SiteContext $site, private readonly SeoManager $seo) {}

    /**
     * The `@graph` for the current page.
     *
     * @return array<string, mixed>
     */
    public function graph(): array
    {
        $graph = [$this->organization(), $this->website()];

        if (($crumbs = $this->breadcrumbs()) !== null) {
            $graph[] = $crumbs;
        }

        return ['@context' => 'https://schema.org', '@graph' => [...$graph, ...$this->seo->schemaNodes()]];
    }

    public function json(): string
    {
        return (string) json_encode($this->graph(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG);
    }

    /**
     * Organisation logo for Google: Settings → SEO logo, else the site logo.
     */
    public function logoUrl(): string
    {
        $id = app(SeoSettings::class)->search_logo_asset_id;
        $asset = $id ? Asset::query()->with('media')->find($id) : null;

        return $asset?->url('md') ?: $this->site->logoUrl();
    }

    public function organizationId(): string
    {
        return url('/').'#organization';
    }

    /**
     * @return array<string, mixed>
     */
    public function organization(): array
    {
        $contact = app(ContactSettings::class);
        $company = app(CompanySettings::class);

        $extra = array_filter((array) app(SeoSettings::class)->schema_extra, fn ($key) => is_string($key) && ! str_starts_with($key, '@'), ARRAY_FILTER_USE_KEY);

        return array_filter([
            ...$extra,
            '@type' => 'GeneralContractor',
            '@id' => $this->organizationId(),
            'name' => $this->site->name(),
            'legalName' => $this->site->legalName() ?: null,
            'url' => $this->site->homeUrl(),
            'logo' => $this->logoUrl(),
            'image' => $this->logoUrl(),
            'description' => $this->site->footerAbout() ?: null,
            'telephone' => $contact->phones[0] ?? null,
            'email' => $contact->emails[0] ?? null,
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $this->site->address() ?: null,
                'addressLocality' => app()->getLocale() === 'ar' ? 'أبوظبي' : 'Abu Dhabi',
                'addressCountry' => 'AE',
            ],
            'geo' => $contact->map_lat && $contact->map_lng ? ['@type' => 'GeoCoordinates', 'latitude' => $contact->map_lat, 'longitude' => $contact->map_lng] : null,
            'openingHours' => array_map(fn (array $row) => $row['days'].' '.$row['hours'], $this->site->workingHours()) ?: null,
            'sameAs' => array_values($this->site->social()) ?: null,
            'identifier' => filled($company->trade_licence_no) ? ['@type' => 'PropertyValue', 'propertyID' => 'Trade licence', 'value' => $company->trade_licence_no] : null,
            'foundingDate' => $company->founded_year ? (string) $company->founded_year : null,
            'areaServed' => ['@type' => 'Country', 'name' => 'United Arab Emirates'],
        ], fn ($value) => $value !== null);
    }

    /**
     * @return array<string, mixed>
     */
    public function website(): array
    {
        return array_filter([
            '@type' => 'WebSite',
            '@id' => url('/').'#website',
            'url' => $this->site->homeUrl(),
            'name' => $this->seo->siteName(),
            'alternateName' => array_values(array_unique(array_filter([
                Translate::pick(app(SeoSettings::class)->search_alternate_name),
                $this->site->name(),
            ], fn (string $name) => $name !== '' && $name !== $this->seo->siteName()))) ?: null,
            'inLanguage' => app()->getLocale(),
            'publisher' => ['@id' => $this->organizationId()],
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => ['@type' => 'EntryPoint', 'urlTemplate' => LocalizedRoute::url('search').'?q={search_term_string}'],
                'query-input' => 'required name=search_term_string',
            ],
        ], fn ($value) => $value !== null);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function breadcrumbs(): ?array
    {
        $trail = $this->seo->breadcrumbs();

        if ($trail === []) {
            return null;
        }

        $items = [];
        $position = 1;

        foreach ([__('site.home') => $this->site->homeUrl(), ...$trail] as $label => $url) {
            $items[] = array_filter([
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => (string) $label,
                'item' => $url ?? $this->seo->canonical(),
            ]);
        }

        return ['@type' => 'BreadcrumbList', 'itemListElement' => $items];
    }

    /**
     * @return array<string, mixed>
     */
    public function service(Service $service): array
    {
        return array_filter([
            '@type' => 'Service',
            'name' => $service->title,
            'description' => $service->seoDescription(),
            'url' => $service->localizedUrl(),
            'image' => $service->firstAsset('cover')?->url('lg'),
            'provider' => ['@id' => $this->organizationId()],
            'areaServed' => ['@type' => 'Country', 'name' => 'United Arab Emirates'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function article(Post $post): array
    {
        return array_filter([
            '@type' => 'Article',
            'headline' => Str::limit((string) $post->getTranslation('title', app()->getLocale()), 110, ''),
            'description' => $post->seoDescription(),
            'image' => $post->firstAsset('cover')?->url('lg'),
            'datePublished' => $post->published_at?->toIso8601String(),
            'dateModified' => $post->updated_at?->toIso8601String(),
            'inLanguage' => app()->getLocale(),
            'mainEntityOfPage' => $post->localizedUrl(),
            'author' => ['@id' => $this->organizationId()],
            'publisher' => ['@id' => $this->organizationId()],
            'articleSection' => $post->category?->name,
        ]);
    }

    /**
     * @param  list<array{q: string, a: string}>  $items
     * @return array<string, mixed>|null
     */
    public function faq(array $items): ?array
    {
        $items = array_values(array_filter($items, fn (array $item) => $item['q'] !== '' && $item['a'] !== ''));

        if ($items === []) {
            return null;
        }

        return [
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn (array $item) => [
                '@type' => 'Question',
                'name' => $item['q'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags($item['a'])],
            ], $items),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function jobPosting(JobPost $job): array
    {
        $employment = match ($job->job_type->value) {
            'part_time' => 'PART_TIME',
            'contract' => 'CONTRACTOR',
            default => 'FULL_TIME',
        };

        return array_filter([
            '@type' => 'JobPosting',
            'title' => $job->title,
            'description' => (string) ($job->getTranslation('description', app()->getLocale()) ?: $job->getTranslation('title', app()->getLocale())),
            'datePosted' => $job->published_at?->toDateString(),
            'validThrough' => $job->closes_at?->endOfDay()->toIso8601String(),
            'employmentType' => $employment,
            'hiringOrganization' => ['@type' => 'Organization', 'name' => $this->site->name(), 'sameAs' => $this->site->homeUrl(), 'logo' => $this->site->logoUrl()],
            'jobLocation' => [
                '@type' => 'Place',
                'address' => ['@type' => 'PostalAddress', 'addressLocality' => $job->location ?: 'Abu Dhabi', 'addressCountry' => 'AE'],
            ],
            'directApply' => true,
        ]);
    }

    /**
     * Project page: a CreativeWork with its gallery as ImageObjects.
     *
     * @param  iterable<Asset>  $images
     * @return array<string, mixed>
     */
    public function project(Project $project, iterable $images): array
    {
        $objects = [];

        foreach ($images as $image) {
            if ($image->isImage() && ($url = $image->url('lg'))) {
                $objects[] = array_filter([
                    '@type' => 'ImageObject',
                    'contentUrl' => $url,
                    'caption' => $image->captionText() ?: $image->altText(),
                    'width' => $image->width,
                    'height' => $image->height,
                ]);
            }
        }

        return array_filter([
            '@type' => 'CreativeWork',
            'name' => $project->title,
            'description' => $project->seoDescription(),
            'url' => $project->localizedUrl(),
            'locationCreated' => $project->location ? ['@type' => 'Place', 'name' => $project->location] : null,
            'creator' => ['@id' => $this->organizationId()],
            'image' => $objects ?: null,
        ]);
    }
}
