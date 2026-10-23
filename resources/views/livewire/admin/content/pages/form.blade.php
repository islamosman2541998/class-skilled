<div x-data="dirtyGuard(@js(__('admin.common.unsaved_changes')))">
    @include('livewire.admin.content.partials.header', ['listLabel' => __('admin.nav.pages'), 'listRoute' => 'admin.pages.index'])

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
        <div class="min-w-0 space-y-6">
            {{-- Header --}}
            <x-admin.panel :title="__('admin.pages.header')">
                <x-admin.locale-tabs :missing="$this->missingEnglish([$form['title'], $form['excerpt']])">
                    @foreach (['ar', 'en'] as $loc)
                        <x-slot :name="$loc">
                            <x-admin.input model="form.title.{{ $loc }}" :label="__('admin.content.fields.title')" :required="$loc === 'ar'" />
                            <x-admin.slug model="form.slug.{{ $loc }}" :prefix="$this->slugPrefix('pages.show', $loc)" :hint="__('admin.content.slug_hint')" />
                            <x-admin.textarea model="form.excerpt.{{ $loc }}" :label="__('admin.content.fields.description')" rows="3" :max="300" />
                        </x-slot>
                    @endforeach
                </x-admin.locale-tabs>

                <div class="mt-6 space-y-4 border-t border-a-border pt-6">
                    <x-admin.segmented model="form.hero_type" live :label="__('admin.pages.main_media')"
                        :options="collect(\App\Enums\HeroMediaType::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all()" />
                    @switch($form['hero_type'])
                        @case('image')
                            <div class="grid gap-5 md:grid-cols-2">
                                <livewire:admin.asset-field wire:model="form.hero.image" kind="image" :label="__('admin.pages.hero_image')" :required="true" :error="$errors->first('form.hero.image')" wire:key="hero-image" />
                                <livewire:admin.asset-field wire:model="form.hero.mobile" kind="image" :label="__('admin.pages.hero_mobile')" :hint="__('admin.pages.hero_mobile_hint')" wire:key="hero-mobile" />
                            </div>
                            @break
                        @case('video')
                            <livewire:admin.asset-field wire:model="form.hero.video" kind="video" :label="__('admin.pages.hero_video')" :required="true" :error="$errors->first('form.hero.video')" wire:key="hero-video" />
                            @break
                        @case('embed')
                            <livewire:admin.asset-field wire:model="form.hero.embed" kind="embed" :label="__('admin.pages.hero_embed')" :hint="__('admin.pages.hero_embed_hint')" :required="true" :error="$errors->first('form.hero.embed')" wire:key="hero-embed" />
                            @break
                        @case('pdf')
                            <livewire:admin.asset-field wire:model="form.hero.pdf" kind="document" :label="__('admin.pages.hero_pdf')" :required="true" :error="$errors->first('form.hero.pdf')" wire:key="hero-pdf" />
                            @break
                    @endswitch
                </div>
            </x-admin.panel>

            @include('livewire.admin.content.partials.blocks')

            {{-- Attachments: files → videos → images --}}
            <x-admin.panel :title="__('admin.pages.attachments')" :subtitle="__('admin.pages.attachments_hint')">
                <div class="space-y-4">
                    @foreach (['files' => 'document', 'videos' => null, 'images' => 'image'] as $group => $kind)
                        <div class="rounded-xl border border-a-border p-4" wire:key="att-{{ $group }}">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <p class="font-bold text-[var(--c-deep)]">{{ __('admin.pages.groups.'.$group) }}</p>
                                <x-admin.toggle model="form.attachments.{{ $group }}.visible" :label="__('admin.pages.group_visible')" />
                            </div>
                            <div class="mt-4 grid gap-4 md:grid-cols-[minmax(0,1fr)_220px]">
                                <x-admin.pair model="form.attachments.{{ $group }}.title" :label="__('admin.pages.group_title')" />
                                @if ($group === 'images')
                                    <div class="space-y-3">
                                        <x-admin.segmented model="form.attachments.images.display" :label="__('admin.pages.display')" :options="['grid' => __('admin.pages.display_grid'), 'masonry' => __('admin.pages.display_masonry')]" />
                                        <x-admin.segmented model="form.attachments.images.columns" :label="__('admin.blocks.fields.columns')" :options="[2 => '2', 3 => '3', 4 => '4']" />
                                    </div>
                                @else
                                    <p class="text-[13px] text-[var(--a-muted)]">{{ __('admin.pages.display_'.($group === 'files' ? 'list' : 'grid')) }}</p>
                                @endif
                            </div>
                            <div class="mt-4">
                                <livewire:admin.asset-field wire:model.live="form.att.{{ $group }}" multiple :kind="$kind" :label="__('admin.pages.items')" wire:key="att-field-{{ $group }}" />
                            </div>
                            @if (! empty($form['att'][$group]))
                                <div class="mt-4 space-y-2">
                                    <p class="a-label">{{ __('admin.pages.title_overrides') }}</p>
                                    @foreach ($form['att'][$group] as $assetId)
                                        <div class="grid items-center gap-2 md:grid-cols-[180px_minmax(0,1fr)]" wire:key="att-title-{{ $group }}-{{ $assetId }}">
                                            <span class="truncate text-[13px] text-[var(--a-muted)]" dir="auto">{{ $assetTitles[$assetId] ?? '#'.$assetId }}</span>
                                            <x-admin.pair model="form.att_titles.{{ $assetId }}" />
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </x-admin.panel>
        </div>

        <aside class="space-y-6">
            @include('livewire.admin.content.partials.publish', ['extra' => 'livewire.admin.content.pages.side'])
            @include('livewire.admin.content.partials.seo', ['fallbackTitle' => $form['title'], 'fallbackDescription' => $form['excerpt']])
        </aside>
    </div>
</div>
