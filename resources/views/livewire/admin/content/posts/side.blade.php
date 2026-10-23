<x-admin.select model="form.post_category_id" :label="__('admin.posts.category')" :options="$categories" />
<x-admin.select model="form.author_id" :label="__('admin.posts.author')" :options="$authors" />
<livewire:admin.asset-field wire:model="form.cover" kind="image" :label="__('admin.content.cover')" wire:key="post-cover" />
<x-admin.toggle model="form.is_featured" :label="__('admin.content.featured')" />
@if ($readingMinutes)
    <p class="text-[13px] text-[var(--a-muted)]">{{ __('admin.posts.reading', ['count' => $readingMinutes]) }}</p>
@endif
