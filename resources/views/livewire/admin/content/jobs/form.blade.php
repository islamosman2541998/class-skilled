<div x-data="dirtyGuard(@js(__('admin.common.unsaved_changes')))">
    @include('livewire.admin.content.partials.header', ['listLabel' => __('admin.nav.jobs'), 'listRoute' => 'admin.jobs.index'])

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
        <div class="min-w-0 space-y-6">
            <x-admin.panel :title="__('admin.content.main_info')">
                <x-admin.locale-tabs :missing="$this->missingEnglish([$form['title'], $form['department'], $form['description']])">
                    @foreach (['ar', 'en'] as $loc)
                        <x-slot :name="$loc">
                            <x-admin.input model="form.title.{{ $loc }}" :label="__('admin.content.fields.title')" :required="$loc === 'ar'" />
                            <x-admin.slug model="form.slug.{{ $loc }}" :prefix="$this->slugPrefix('careers.show', $loc)" :hint="__('admin.content.slug_hint')" />
                            <div class="grid gap-4 sm:grid-cols-2">
                                <x-admin.input model="form.department.{{ $loc }}" :label="__('admin.jobs.department')" />
                                <x-admin.input model="form.location.{{ $loc }}" :label="__('admin.jobs.location')" />
                            </div>
                            <x-admin.rich-text model="form.description.{{ $loc }}" :label="__('admin.jobs.description')" :required="$loc === 'ar'" :dir="$loc === 'ar' ? 'rtl' : 'ltr'" />
                        </x-slot>
                    @endforeach
                </x-admin.locale-tabs>
            </x-admin.panel>

            <x-admin.panel :title="__('admin.jobs.requirements')">
                @include('livewire.admin.content.partials.pair-list', ['path' => 'form.requirements', 'label' => null])
            </x-admin.panel>
        </div>

        <aside class="space-y-6">
            @include('livewire.admin.content.partials.publish', ['extra' => 'livewire.admin.content.jobs.side'])
            @include('livewire.admin.content.partials.seo', ['fallbackTitle' => $form['title'], 'fallbackDescription' => ['ar' => strip_tags($form['description']['ar'] ?? ''), 'en' => strip_tags($form['description']['en'] ?? '')]])
        </aside>
    </div>
</div>
