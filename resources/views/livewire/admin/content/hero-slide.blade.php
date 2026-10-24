@php
    $back = route('admin.home-sections.index', ['edit' => $section->id]);
    $title = $slideId ? __('admin.home.edit_slide') : __('admin.home.add_slide');
@endphp
<div x-data="dirtyGuard(@js(__('admin.common.unsaved_changes')))">
    <x-admin.page-header :title="$title" :subtitle="__('admin.home.slide_page_hint')"
        :breadcrumbs="[__('admin.nav.content') => null, __('admin.nav.home_sections') => $back, __('admin.home.slides') => $back, $title => null]">
        <x-slot:actions>
            <a href="{{ $back }}" wire:navigate class="a-btn a-btn-secondary">{{ __('admin.common.back') }}</a>
            <button type="submit" form="slide-form" class="a-btn a-btn-primary" wire:loading.attr="disabled" wire:target="save">{{ __('admin.common.save') }}</button>
        </x-slot:actions>
    </x-admin.page-header>

    <form id="slide-form" wire:submit="save" class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_420px]">
        <div class="min-w-0 space-y-6">
            <x-admin.panel :title="__('admin.home.slide_texts')">
                <x-admin.locale-tabs :missing="$this->missingEnglish([$slide['title'], $slide['text'], $slide['caption']])">
                    <x-slot:ar>
                        <x-admin.input model="slide.title.ar" :label="__('admin.home.slide_title')" required live />
                        <x-admin.textarea model="slide.text.ar" :label="__('admin.home.slide_text')" :max="400" rows="3" live />
                        <x-admin.input model="slide.caption.ar" :label="__('admin.home.slide_caption')" :hint="__('admin.home.slide_caption_hint')" live />
                    </x-slot:ar>
                    <x-slot:en>
                        <x-admin.input model="slide.title.en" :label="__('admin.home.slide_title')" dir="ltr" />
                        <x-admin.textarea model="slide.text.en" :label="__('admin.home.slide_text')" :max="400" rows="3" dir="ltr" />
                        <x-admin.input model="slide.caption.en" :label="__('admin.home.slide_caption')" dir="ltr" />
                    </x-slot:en>
                </x-admin.locale-tabs>
            </x-admin.panel>

            <div class="grid gap-6 lg:grid-cols-2">
                @foreach (['primary', 'secondary'] as $button)
                    <x-admin.panel :title="__('admin.blocks.fields.'.$button.'_button')">
                        <div class="space-y-4">
                            <x-admin.pair model="slide.{{ $button }}.label" :label="__('admin.home.button_label')" />
                            <x-admin.link-picker model="slide.{{ $button }}.link" :label="__('admin.home.button_link')" :options="$this->linkOptions" />
                        </div>
                    </x-admin.panel>
                @endforeach
            </div>
        </div>

        <aside class="min-w-0 space-y-6 xl:sticky xl:top-24">
            <x-admin.panel :title="__('admin.home.slide_preview')">
                <div class="relative aspect-[16/10] overflow-hidden rounded-xl bg-[#1b2a47]" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
                    @if ($previewImage)
                        <img src="{{ $previewImage }}" alt="" class="absolute inset-0 h-full w-full object-cover">
                    @endif
                    <div class="absolute inset-0" style="background: rgb(27 42 71 / {{ max(30, min(60, (int) $slide['overlay'])) / 100 }})"></div>
                    <div class="absolute inset-x-4 bottom-4 text-white [text-shadow:0_2px_12px_rgb(0_0_0/.4)]">
                        @if (filled($slide['caption']['ar']))
                            <span class="mb-1.5 inline-block rounded-full bg-white/15 px-2 py-0.5 text-[10.5px] font-semibold">{{ $slide['caption']['ar'] }}</span>
                        @endif
                        <p class="font-[family-name:var(--font-head)] text-[17px] leading-snug font-extrabold">{{ $slide['title']['ar'] ?: __('admin.home.slide_title') }}</p>
                        @if (filled($slide['text']['ar']))
                            <p class="mt-1 line-clamp-2 text-[11.5px] text-white/85">{{ $slide['text']['ar'] }}</p>
                        @endif
                    </div>
                </div>
                <div class="mt-4">
                    <label for="overlay" class="a-label">{{ __('admin.home.overlay') }} <span class="font-normal text-[var(--a-muted)]" dir="ltr">({{ (int) $slide['overlay'] }}%)</span></label>
                    <input id="overlay" type="range" min="30" max="60" step="5" wire:model.live="slide.overlay" class="w-full accent-[var(--c-navy)]">
                    <p class="a-hint">{{ __('admin.home.overlay_hint') }}</p>
                    @error('slide.overlay')<p class="a-error">{{ $message }}</p>@enderror
                </div>
            </x-admin.panel>

            <x-admin.panel :title="__('admin.home.slide_media')">
                <div class="space-y-5">
                    <livewire:admin.asset-field wire:model.live="slide.image_asset_id" kind="image" :label="__('admin.home.slide_image')" :hint="__('admin.home.slide_image_hint')" :required="true" :error="$errors->first('slide.image_asset_id')" wire:key="slide-image" />
                    <livewire:admin.asset-field wire:model="slide.mobile_image_asset_id" kind="image" :label="__('admin.home.slide_mobile')" :hint="__('admin.home.slide_mobile_hint')" wire:key="slide-mobile" />
                    <livewire:admin.asset-field wire:model="slide.video_asset_id" kind="video" :label="__('admin.home.slide_video')" :hint="__('admin.home.slide_video_hint')" wire:key="slide-video" />
                </div>
            </x-admin.panel>
        </aside>
    </form>
</div>
