<div x-data="dirtyGuard(@js(__('admin.common.unsaved_changes')))">
    @include('livewire.admin.content.partials.header', ['listLabel' => __('admin.nav.posts'), 'listRoute' => 'admin.posts.index'])

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
        <div class="min-w-0 space-y-6">
            <x-admin.panel :title="__('admin.content.main_info')">
                <x-admin.locale-tabs :missing="$this->missingEnglish([$form['title'], $form['excerpt'], $form['body']])">
                    @foreach (['ar', 'en'] as $loc)
                        <x-slot :name="$loc">
                            <x-admin.input model="form.title.{{ $loc }}" :label="__('admin.content.fields.title')" :required="$loc === 'ar'" />
                            <x-admin.slug model="form.slug.{{ $loc }}" :prefix="$this->slugPrefix('posts.show', $loc)" :hint="__('admin.content.slug_hint')" />
                            <x-admin.textarea model="form.excerpt.{{ $loc }}" :label="__('admin.content.fields.excerpt')" rows="3" :max="300" />
                            <x-admin.rich-text model="form.body.{{ $loc }}" :label="__('admin.posts.body')" :required="$loc === 'ar'" :dir="$loc === 'ar' ? 'rtl' : 'ltr'" />
                        </x-slot>
                    @endforeach
                </x-admin.locale-tabs>
            </x-admin.panel>
        </div>

        <aside class="space-y-6">
            @include('livewire.admin.content.partials.publish', ['extra' => 'livewire.admin.content.posts.side'])
            @include('livewire.admin.content.partials.seo', ['fallbackTitle' => $form['title'], 'fallbackDescription' => $form['excerpt']])
        </aside>
    </div>
</div>
