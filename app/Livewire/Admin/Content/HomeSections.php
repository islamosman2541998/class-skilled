<?php

namespace App\Livewire\Admin\Content;

use App\Enums\DataSource;
use App\Enums\SectionBackground;
use App\Enums\SectionType;
use App\Livewire\Admin\Concerns\WithRepeaters;
use App\Livewire\Admin\Concerns\WithToasts;
use App\Livewire\Admin\Concerns\WithTranslatableForm;
use App\Models\Certificate;
use App\Models\Client;
use App\Models\HomeSection;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Models\Testimonial;
use App\Services\HomeSectionResolver;
use App\Support\LinkOptions;
use App\Support\LinkResolver;
use App\Support\Translate;
use Database\Seeders\DefaultHomeSections;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Home page builder: ordered sections (drag), visibility, add / delete, and a
 * side-panel form per section type. All texts AR/EN.
 */
#[Layout('layouts.admin')]
class HomeSections extends Component
{
    use WithRepeaters;
    use WithToasts;
    use WithTranslatableForm;

    public const LIST_TYPES = ['services', 'projects', 'posts', 'testimonials', 'clients', 'certificates'];

    /** Repeater lists inside `data` per type. */
    public const LISTS = ['hero' => ['slides'], 'feature_band' => ['bullets'], 'why_us' => ['items'], 'process' => ['steps'], 'stats' => ['items']];

    public ?int $editing = null;

    /** @var array<string, mixed> */
    public array $section = [];

    public function mount(): void
    {
        $this->authorize('viewAny', HomeSection::class);
    }

    /**
     * @return Collection<int, HomeSection>
     */
    #[Computed]
    public function sections(): Collection
    {
        return HomeSection::query()->orderBy('sort')->orderBy('id')->get();
    }

    /**
     * @return array{routes: array<string, string>, models: array<string, array{label: string, items: array<int, string>}>}
     */
    #[Computed]
    public function linkOptions(): array
    {
        return LinkOptions::all();
    }

    public function edit(int $id): void
    {
        $model = HomeSection::query()->findOrFail($id);
        $this->authorize('update', $model);
        $this->editing = $model->id;

        $data = (array) ($model->data ?? []);

        foreach (self::LISTS[$model->type->value] ?? [] as $list) {
            $data[$list] = $this->withRepeaterKeys((array) ($data[$list] ?? []));
        }

        if (in_array($model->type->value, self::LIST_TYPES, true)) {
            $data['item_ids'] = array_map('strval', (array) ($data['item_ids'] ?? []));
        }

        $this->section = [
            'type' => $model->type->value,
            'title' => Translate::pair($model->getTranslations('title')),
            'subtitle' => Translate::pair($model->getTranslations('subtitle')),
            'cta_label' => Translate::pair($model->getTranslations('cta_label')),
            'cta_link' => $model->cta_link,
            'source' => $model->source->value,
            'items_limit' => $model->items_limit,
            'background' => $model->background->value,
            'is_visible' => $model->is_visible,
            'data' => $data,
        ];

        $this->resetErrorBag();
    }

    public function close(): void
    {
        $this->editing = null;
        $this->section = [];
    }

    public function add(string $type): void
    {
        $this->authorize('create', HomeSection::class);
        $sectionType = SectionType::tryFrom($type);

        if ($sectionType === null) {
            return;
        }

        $defaults = collect(DefaultHomeSections::all())->first(fn (array $s) => $s['type'] === $sectionType) ?? [];

        $model = HomeSection::query()->create(array_merge([
            'source' => DataSource::All,
            'background' => SectionBackground::White,
            'items_limit' => in_array($type, self::LIST_TYPES, true) ? 8 : null,
            'data' => [],
        ], $defaults, [
            'type' => $sectionType,
            'is_visible' => false,
            'sort' => (int) HomeSection::query()->max('sort') + 1,
        ]));

        unset($this->sections);
        $this->edit($model->id);
        $this->toast('success', __('admin.home.added'));
    }

    public function toggleVisible(int $id): void
    {
        $model = HomeSection::query()->findOrFail($id);
        $this->authorize('update', $model);
        $model->update(['is_visible' => ! $model->is_visible]);

        if ($this->editing === $id) {
            $this->section['is_visible'] = $model->is_visible;
        }

        unset($this->sections);
        $this->toast('success', __('admin.common.updated'));
    }

    /**
     * @param  array<int, int|string>  $ids
     */
    public function reorder(array $ids): void
    {
        $this->authorize('update', new HomeSection);
        HomeSection::saveOrder($ids);
        unset($this->sections);
        $this->toast('success', __('admin.common.reordered'));
    }

    public function delete(int $id): void
    {
        $model = HomeSection::query()->findOrFail($id);
        $this->authorize('delete', $model);
        $model->delete();

        if ($this->editing === $id) {
            $this->close();
        }

        unset($this->sections);
        $this->toast('success', __('admin.common.deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $type = $this->section['type'] ?? '';
        $rules = [
            ...$this->pairRules('section.title', false, 200),
            ...$this->pairRules('section.subtitle', false, 600),
            ...$this->pairRules('section.cta_label', false, 80),
            'section.cta_link' => ['nullable', 'array'],
            'section.is_visible' => ['boolean'],
            'section.background' => ['required', Rule::in(SectionBackground::values())],
        ];

        $pair = fn (string $key, bool $required = false, int $max = 300) => $this->pairRules($key, $required, $max);

        switch ($type) {
            case 'hero':
                $rules += [
                    'section.data.autoplay' => ['required', 'integer', 'min:4', 'max:10'],
                    'section.data.show_arrows' => ['boolean'],
                    'section.data.show_progress' => ['boolean'],
                    'section.data.slides' => ['array', 'min:1'],
                    'section.data.slides.*.image_asset_id' => ['required', 'integer', 'exists:assets,id'],
                    'section.data.slides.*.mobile_image_asset_id' => ['nullable', 'integer', 'exists:assets,id'],
                    'section.data.slides.*.video_asset_id' => ['nullable', 'integer', 'exists:assets,id'],
                    'section.data.slides.*.overlay' => ['required', 'integer', 'min:30', 'max:60'],
                    ...$pair('section.data.slides.*.title', true, 160),
                    ...$pair('section.data.slides.*.text', false, 400),
                    ...$pair('section.data.slides.*.caption', false, 120),
                    ...$pair('section.data.slides.*.primary.label', false, 60),
                    ...$pair('section.data.slides.*.secondary.label', false, 60),
                ];
                break;
            case 'about':
                $rules += [...$pair('section.data.text', false, 1500), 'section.data.image_asset_id' => ['nullable', 'integer', 'exists:assets,id']];
                break;
            case 'feature_band':
                $rules += [...$pair('section.data.bullets.*', true, 160), 'section.data.image_asset_ids' => ['array', 'max:3'], 'section.data.image_asset_ids.*' => ['integer', 'exists:assets,id']];
                break;
            case 'why_us':
            case 'stats':
                if ($type === 'stats') {
                    $rules += ['section.data.items.*.value' => ['required', 'string', 'max:20'], ...$pair('section.data.items.*.suffix', false, 20), ...$pair('section.data.items.*.label', true, 120), 'section.data.count_up' => ['boolean']];
                } else {
                    $rules += [...$pair('section.data.items.*.title', true, 120), ...$pair('section.data.items.*.text', false, 400)];
                }
                break;
            case 'process':
                $rules += [...$pair('section.data.steps.*.title', true, 120), ...$pair('section.data.steps.*.text', false, 400)];
                break;
            case 'cta':
                $rules += [...$pair('section.data.primary.label', false, 60), ...$pair('section.data.secondary.label', false, 60)];
                break;
            case 'rich_text':
                $rules += $pair('section.data.html', true, 30000);
                break;
            default:
                if (in_array($type, self::LIST_TYPES, true)) {
                    $rules += [
                        'section.source' => ['required', Rule::in(DataSource::values())],
                        'section.items_limit' => ['required', 'integer', 'min:1', 'max:24'],
                        'section.data.item_ids' => ['array', Rule::requiredIf(($this->section['source'] ?? '') === 'manual')],
                        'section.data.display' => ['required', 'in:slider,grid'],
                        'section.data.autoplay' => ['boolean'],
                        'section.data.bg_asset_id' => ['nullable', 'integer', 'exists:assets,id'],
                    ];
                }
        }

        return $rules;
    }

    public function save(): void
    {
        $model = HomeSection::query()->findOrFail((int) $this->editing);
        $this->authorize('update', $model);
        $this->validate();

        $data = $this->stripRepeaterKeys((array) ($this->section['data'] ?? []));

        if (in_array($model->type->value, self::LIST_TYPES, true)) {
            $data['item_ids'] = array_values(array_map('intval', (array) ($data['item_ids'] ?? [])));
            $data['bg_asset_id'] = ! empty($data['bg_asset_id']) ? (int) $data['bg_asset_id'] : null;
        }

        if ($model->type === SectionType::Hero) {
            foreach ($data['slides'] as $i => $slide) {
                foreach (['primary', 'secondary'] as $button) {
                    $data['slides'][$i][$button]['link'] = LinkResolver::normalize($slide[$button]['link'] ?? null);
                }
                $data['slides'][$i]['id'] ??= (string) Str::uuid();
            }
        }

        if ($model->type === SectionType::Cta) {
            foreach (['primary', 'secondary'] as $button) {
                $data[$button]['link'] = LinkResolver::normalize($data[$button]['link'] ?? null);
            }
        }

        if ($model->type === SectionType::RichText) {
            $data['html'] = [
                'ar' => (string) clean((string) ($data['html']['ar'] ?? ''), 'rich'),
                'en' => trim(strip_tags((string) ($data['html']['en'] ?? ''))) === '' ? '' : (string) clean((string) $data['html']['en'], 'rich'),
            ];
        }

        $model->fill([
            'title' => array_filter(Translate::pair($this->section['title'] ?? [])),
            'subtitle' => array_filter(Translate::pair($this->section['subtitle'] ?? [])),
            'cta_label' => array_filter(Translate::pair($this->section['cta_label'] ?? [])),
            'cta_link' => LinkResolver::normalize($this->section['cta_link'] ?? null),
            'source' => $this->section['source'] ?? DataSource::All->value,
            'items_limit' => filled($this->section['items_limit'] ?? null) ? (int) $this->section['items_limit'] : null,
            'background' => $this->section['background'],
            'is_visible' => (bool) $this->section['is_visible'],
            'data' => $data,
        ])->save();

        unset($this->sections);
        $this->dispatch('form-saved');
        $this->toast('success', __('admin.home.saved'));
    }

    /**
     * Options for manual picks of list sections.
     *
     * @return array<int, string>
     */
    public function itemOptions(string $type): array
    {
        $title = fn ($m, string $field = 'title') => (string) $m->getTranslation($field, 'ar', false);

        return match ($type) {
            'services' => Service::query()->ordered()->get()->mapWithKeys(fn ($m) => [$m->id => $title($m)])->all(),
            'projects' => Project::query()->ordered()->get()->mapWithKeys(fn ($m) => [$m->id => $title($m)])->all(),
            'posts' => Post::query()->latest('published_at')->get()->mapWithKeys(fn ($m) => [$m->id => $title($m)])->all(),
            'testimonials' => Testimonial::query()->ordered()->get()->mapWithKeys(fn ($m) => [$m->id => $title($m, 'name')])->all(),
            'clients' => Client::query()->ordered()->get()->mapWithKeys(fn ($m) => [$m->id => $title($m, 'name')])->all(),
            'certificates' => Certificate::query()->ordered()->get()->mapWithKeys(fn ($m) => [$m->id => $title($m, 'name')])->all(),
            default => [],
        };
    }

    public function summary(HomeSection $section): string
    {
        return app(HomeSectionResolver::class)->summary($section);
    }

    public function render(): View
    {
        return view('livewire.admin.content.home-sections.index')->title(__('admin.nav.home_sections'));
    }
}
