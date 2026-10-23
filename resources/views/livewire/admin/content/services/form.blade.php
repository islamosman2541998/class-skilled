<div x-data="dirtyGuard(@js(__('admin.common.unsaved_changes')))">
    @include('livewire.admin.content.partials.header', ['listLabel' => __('admin.nav.services'), 'listRoute' => 'admin.services.index', 'publishable' => false])

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
        <div class="min-w-0 space-y-6">
            <x-admin.panel :title="__('admin.content.main_info')">
                <x-admin.locale-tabs :missing="$this->missingEnglish([$form['title'], $form['summary'], $form['excerpt']])">
                    @foreach (['ar', 'en'] as $loc)
                        <x-slot :name="$loc">
                            <x-admin.input model="form.title.{{ $loc }}" :label="__('admin.content.fields.title')" :required="$loc === 'ar'" />
                            <x-admin.slug model="form.slug.{{ $loc }}" :prefix="$this->slugPrefix('services.show', $loc)" :hint="__('admin.content.slug_hint')" />
                            <x-admin.textarea model="form.summary.{{ $loc }}" :label="__('admin.services.summary')" :hint="__('admin.services.summary_hint')" rows="2" :max="140" />
                            <x-admin.textarea model="form.excerpt.{{ $loc }}" :label="__('admin.content.fields.excerpt')" rows="3" :max="300" />
                        </x-slot>
                    @endforeach
                </x-admin.locale-tabs>
            </x-admin.panel>

            <x-admin.panel :title="__('admin.content.images')">
                <div class="grid gap-6 lg:grid-cols-[220px_minmax(0,1fr)]">
                    <livewire:admin.asset-field wire:model="form.cover" kind="image" :label="__('admin.content.cover')" :hint="__('admin.services.cover_hint')" wire:key="cover" />
                    <livewire:admin.asset-field wire:model="form.gallery" multiple kind="image" :label="__('admin.content.gallery')" wire:key="gallery" />
                </div>
            </x-admin.panel>

            <x-admin.panel :title="__('admin.services.scope_stages')">
                <div class="grid gap-6 xl:grid-cols-2">
                    @include('livewire.admin.content.partials.pair-list', ['path' => 'form.scope', 'label' => __('admin.services.scope')])
                    @include('livewire.admin.content.partials.pair-list', ['path' => 'form.stages', 'label' => __('admin.services.stages'), 'numbered' => true])
                </div>
            </x-admin.panel>

            @include('livewire.admin.content.partials.blocks')

            <x-admin.panel :title="__('admin.services.faqs')" :subtitle="__('admin.services.faqs_hint')">
                <x-admin.repeater model="form.faqs" :template="['id' => null, 'q' => ['ar' => '', 'en' => ''], 'a' => ['ar' => '', 'en' => '']]">
                    @foreach ($form['faqs'] as $j => $faq)
                        <x-admin.repeater-item model="form.faqs" :index="$j" :item="$faq" :title="$faq['q']['ar'] ?? ''" :open="blank($faq['q']['ar'] ?? '')">
                            <x-admin.pair model="form.faqs.{{ $j }}.q" :label="__('admin.blocks.fields.question')" required />
                            <x-admin.pair model="form.faqs.{{ $j }}.a" :label="__('admin.blocks.fields.answer')" textarea rows="3" required />
                        </x-admin.repeater-item>
                    @endforeach
                </x-admin.repeater>
            </x-admin.panel>
        </div>

        <aside class="space-y-6">
            @include('livewire.admin.content.partials.publish', ['publishable' => false, 'extra' => 'livewire.admin.content.services.side'])
            @include('livewire.admin.content.partials.seo', ['fallbackTitle' => $form['title'], 'fallbackDescription' => $form['excerpt']])
        </aside>
    </div>
</div>
