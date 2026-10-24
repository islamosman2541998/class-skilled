<?php

namespace App\Livewire\Admin\Content;

use App\Enums\SectionType;
use App\Livewire\Admin\Concerns\WithToasts;
use App\Livewire\Admin\Concerns\WithTranslatableForm;
use App\Models\Asset;
use App\Models\HomeSection;
use App\Support\LinkOptions;
use App\Support\LinkResolver;
use App\Support\Translate;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * One hero slide on its own page (add or edit): image / mobile image / video,
 * texts AR/EN, two buttons and the overlay strength, with a live preview.
 * Slides are stored in the hero section's `data.slides`, identified by `id`.
 */
#[Layout('layouts.admin')]
class HeroSlide extends Component
{
    use WithToasts;
    use WithTranslatableForm;

    #[Locked]
    public int $sectionId;

    #[Locked]
    public ?string $slideId = null;

    /** @var array<string, mixed> */
    public array $slide = [];

    public function mount(int $section, ?string $slideKey = null): void
    {
        $model = $this->section($section);
        $this->authorize('update', $model);
        $this->sectionId = $model->id;

        if ($slideKey === null) {
            $this->slide = self::blank();

            return;
        }

        $found = collect((array) ($model->data['slides'] ?? []))->firstWhere('id', $slideKey);
        abort_if($found === null, 404);

        $this->slideId = $slideKey;
        $this->slide = array_replace_recursive(self::blank(), $found);
        foreach (['title', 'text', 'caption'] as $field) {
            $this->slide[$field] = Translate::pair($this->slide[$field]);
        }
        foreach (['primary', 'secondary'] as $button) {
            $this->slide[$button]['label'] = Translate::pair($this->slide[$button]['label'] ?? []);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public static function blank(): array
    {
        return [
            'image_asset_id' => null,
            'mobile_image_asset_id' => null,
            'video_asset_id' => null,
            'title' => ['ar' => '', 'en' => ''],
            'text' => ['ar' => '', 'en' => ''],
            'caption' => ['ar' => '', 'en' => ''],
            'primary' => ['label' => ['ar' => '', 'en' => ''], 'link' => null],
            'secondary' => ['label' => ['ar' => '', 'en' => ''], 'link' => null],
            'overlay' => 45,
        ];
    }

    private function section(?int $id = null): HomeSection
    {
        $model = HomeSection::query()->findOrFail($id ?? $this->sectionId);
        abort_unless($model->type === SectionType::Hero, 404);

        return $model;
    }

    /**
     * @return array{routes: array<string, string>, models: array<string, array{label: string, items: array<int, string>}>}
     */
    #[Computed]
    public function linkOptions(): array
    {
        return LinkOptions::all();
    }

    /**
     * Image URL for the live preview (resolved on every render so a picked or removed image shows at once).
     */
    private function previewImage(): ?string
    {
        $id = $this->slide['image_asset_id'] ?? null;

        return filled($id) ? Asset::query()->with('media')->find((int) $id)?->url('lg') : null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'slide.image_asset_id' => ['required', 'integer', 'exists:assets,id'],
            'slide.mobile_image_asset_id' => ['nullable', 'integer', 'exists:assets,id'],
            'slide.video_asset_id' => ['nullable', 'integer', 'exists:assets,id'],
            'slide.overlay' => ['required', 'integer', 'min:30', 'max:60'],
            ...$this->pairRules('slide.title', true, 160),
            ...$this->pairRules('slide.text', false, 400),
            ...$this->pairRules('slide.caption', false, 120),
            ...$this->pairRules('slide.primary.label', false, 60),
            ...$this->pairRules('slide.secondary.label', false, 60),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'slide.image_asset_id' => __('admin.home.slide_image'),
            'slide.title.ar' => __('admin.home.slide_title'),
            'slide.overlay' => __('admin.home.overlay'),
        ];
    }

    /**
     * A media field picked or removed a file: re-render so the preview follows.
     */
    #[On('asset-field-changed')]
    public function assetFieldChanged(): void {}

    public function save(): void
    {
        $model = $this->section();
        $this->authorize('update', $model);
        $this->validate();

        $slide = [
            'id' => $this->slideId ?? (string) Str::uuid(),
            'image_asset_id' => (int) $this->slide['image_asset_id'],
            'mobile_image_asset_id' => filled($this->slide['mobile_image_asset_id']) ? (int) $this->slide['mobile_image_asset_id'] : null,
            'video_asset_id' => filled($this->slide['video_asset_id']) ? (int) $this->slide['video_asset_id'] : null,
            'title' => Translate::pair($this->slide['title']),
            'text' => Translate::pair($this->slide['text']),
            'caption' => Translate::pair($this->slide['caption']),
            'overlay' => (int) $this->slide['overlay'],
        ];

        foreach (['primary', 'secondary'] as $button) {
            $slide[$button] = [
                'label' => Translate::pair($this->slide[$button]['label'] ?? []),
                'link' => LinkResolver::normalize($this->slide[$button]['link'] ?? null),
            ];
        }

        $data = (array) ($model->data ?? []);
        $slides = array_values((array) ($data['slides'] ?? []));
        $index = $this->slideId === null ? null : collect($slides)->search(fn (array $s) => ($s['id'] ?? null) === $this->slideId);

        if ($index === null || $index === false) {
            $slides[] = $slide;
        } else {
            $slides[$index] = $slide;
        }

        $data['slides'] = $slides;
        $model->forceFill(['data' => $data])->save();

        $this->flashToast('success', __('admin.home.slide_saved'));
        $this->redirectRoute('admin.home-sections.index', ['edit' => $model->id], navigate: true);
    }

    public function render(): View
    {
        return view('livewire.admin.content.hero-slide', [
            'section' => $this->section(),
            'previewImage' => $this->previewImage(),
        ])->title($this->slideId ? __('admin.home.edit_slide') : __('admin.home.add_slide'));
    }
}
