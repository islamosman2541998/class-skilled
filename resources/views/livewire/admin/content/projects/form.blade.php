<div x-data="dirtyGuard(@js(__('admin.common.unsaved_changes')))">
    @include('livewire.admin.content.partials.header', ['listLabel' => __('admin.nav.projects'), 'listRoute' => 'admin.projects.index'])

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
        <div class="min-w-0 space-y-6">
            <x-admin.panel :title="__('admin.content.main_info')">
                <x-admin.locale-tabs :missing="$this->missingEnglish([$form['title'], $form['excerpt'], $form['location'], $form['duration']])">
                    @foreach (['ar', 'en'] as $loc)
                        <x-slot :name="$loc">
                            <x-admin.input model="form.title.{{ $loc }}" :label="__('admin.content.fields.title')" :required="$loc === 'ar'" />
                            <x-admin.slug model="form.slug.{{ $loc }}" :prefix="$this->slugPrefix('projects.show', $loc)" :hint="__('admin.content.slug_hint')" />
                            <x-admin.textarea model="form.excerpt.{{ $loc }}" :label="__('admin.content.fields.excerpt')" rows="3" :max="300" />
                        </x-slot>
                    @endforeach
                </x-admin.locale-tabs>
            </x-admin.panel>

            <x-admin.panel :title="__('admin.projects.facts')">
                <div class="grid gap-5 md:grid-cols-2">
                    <x-admin.select model="form.category_id" :label="__('admin.projects.category')" :options="$categories" />
                    <x-admin.select model="form.service_ids" multiple :label="__('admin.projects.services')" :options="$services" />
                    <x-admin.pair model="form.location" :label="__('admin.projects.location')" />
                    <x-admin.pair model="form.duration" :label="__('admin.projects.duration')" />
                    <x-admin.input model="form.area_m2" type="number" :label="__('admin.projects.area')" step="0.01" />
                    <x-admin.input model="form.year" type="number" :label="__('admin.projects.year')" />
                    <x-admin.segmented model="form.project_status" :label="__('admin.projects.status')" :options="\App\Enums\ProjectStatus::options()" />
                    <div class="space-y-3">
                        <x-admin.pair model="form.client_name" :label="__('admin.projects.client')" />
                        <x-admin.toggle model="form.show_client" :label="__('admin.projects.show_client')" />
                    </div>
                </div>
            </x-admin.panel>

            <x-admin.panel :title="__('admin.content.images')">
                <div class="grid gap-6 lg:grid-cols-[220px_minmax(0,1fr)]">
                    <livewire:admin.asset-field wire:model="form.cover" kind="image" :label="__('admin.content.cover')" wire:key="cover" />
                    <livewire:admin.asset-field wire:model="form.gallery" multiple kind="image,video,embed" :label="__('admin.content.gallery')" :hint="__('admin.content.project_gallery_hint')" wire:key="gallery" />
                </div>
                <div class="mt-6 border-t border-a-border pt-6">
                    <x-admin.repeater model="form.pairs" :label="__('admin.projects.before_after')" :hint="__('admin.projects.before_after_hint')" :template="['before' => null, 'after' => null]">
                        @foreach ($form['pairs'] as $j => $pair)
                            <x-admin.repeater-item model="form.pairs" :index="$j" :item="$pair" :title="__('admin.projects.pair', ['n' => $j + 1])">
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <livewire:admin.asset-field wire:model="form.pairs.{{ $j }}.before" kind="image" :label="__('admin.projects.before')" wire:key="pair-b-{{ $pair['_key'] ?? $j }}-{{ $j }}" :error="$errors->first('form.pairs.'.$j.'.before')" />
                                    <livewire:admin.asset-field wire:model="form.pairs.{{ $j }}.after" kind="image" :label="__('admin.projects.after')" wire:key="pair-a-{{ $pair['_key'] ?? $j }}-{{ $j }}" :error="$errors->first('form.pairs.'.$j.'.after')" />
                                </div>
                            </x-admin.repeater-item>
                        @endforeach
                    </x-admin.repeater>
                </div>
                <div class="mt-6 border-t border-a-border pt-6">
                    <livewire:admin.asset-field wire:model="form.videos" multiple :label="__('admin.projects.videos')" :hint="__('admin.projects.videos_hint')" wire:key="videos" />
                </div>
            </x-admin.panel>

            <x-admin.panel :title="__('admin.projects.scope')">
                @include('livewire.admin.content.partials.pair-list', ['path' => 'form.scope', 'label' => null])
            </x-admin.panel>

            @include('livewire.admin.content.partials.blocks')
        </div>

        <aside class="space-y-6">
            @include('livewire.admin.content.partials.publish', ['extra' => 'livewire.admin.content.projects.side'])
            @include('livewire.admin.content.partials.seo', ['fallbackTitle' => $form['title'], 'fallbackDescription' => $form['excerpt']])
        </aside>
    </div>
</div>
