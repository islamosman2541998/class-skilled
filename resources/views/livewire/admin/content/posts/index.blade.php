<div>
    <x-admin.page-header :title="__('admin.nav.posts')" :subtitle="__('admin.posts.subtitle')" :breadcrumbs="[__('admin.nav.content') => null, __('admin.nav.posts') => null]">
        <x-slot:actions>
            <button type="button" wire:click="export" class="a-btn a-btn-secondary">{{ __('admin.common.export') }}</button>
            @can('create', \App\Models\Post::class)
                <a href="{{ route('admin.posts.create') }}" wire:navigate class="a-btn a-btn-primary">{{ __('admin.posts.add') }}</a>
            @endcan
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.table.status-tabs :tabs="$this->statusTabs()" :value="$filters['status'] ?? ''" />

    @php($rows = $this->rows)
    @php($sort = $this->currentSort())
    <div class="a-panel">
        @include('livewire.admin.content.partials.list-toolbar', ['featurable' => true, 'filtersView' => 'livewire.admin.content.posts.filters'])
        @include('livewire.admin.content.partials.bulk', ['actions' => ['publish', 'unpublish', 'feature', 'unfeature']])
        <div class="overflow-x-auto">
            <table class="a-table">
                <thead>
                    <tr>
                        <th class="w-10"><input type="checkbox" class="a-check" aria-label="{{ __('admin.common.select_page') }}" wire:click="{{ count($selected) && count($selected) >= $rows->count() ? 'clearSelection' : 'selectPage' }}" @checked($rows->count() && count($selected) >= $rows->count())></th>
                        <th>{{ __('admin.content.singular.post') }}</th>
                        <th>{{ __('admin.posts.category') }}</th>
                        <x-admin.table.th field="published" :sort="$sort">{{ __('admin.content.published_at') }}</x-admin.table.th>
                        <th>{{ __('admin.common.translation') }}</th>
                        <th>{{ __('admin.content.featured') }}</th>
                        <th>{{ __('admin.content.published') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody wire:loading.class="opacity-60">
                    @forelse ($rows as $post)
                        <tr wire:key="post-{{ $post->id }}">
                            <td><input type="checkbox" class="a-check" value="{{ $post->id }}" wire:model.live="selected" aria-label="{{ $post->getTranslation('title', 'ar') }}"></td>
                            <td>
                                <div class="flex items-center gap-3">
                                    @if ($cover = $post->firstAsset('cover'))
                                        <x-admin.asset-thumb :asset="$cover" class="h-11 w-14 shrink-0" />
                                    @endif
                                    <a href="{{ route('admin.posts.edit', $post->id) }}" wire:navigate class="font-bold text-[var(--c-deep)] hover:underline">{{ $post->getTranslation('title', app()->getLocale(), false) ?: $post->getTranslation('title', 'ar') }}</a>
                                </div>
                            </td>
                            <td class="text-[13.5px]">{{ $post->category?->getTranslation('name', 'ar') }}</td>
                            <td class="text-[13px] text-[var(--a-muted)]">{{ $post->published_at?->format('Y-m-d') }}</td>
                            <td><x-admin.badge :color="$this->englishMissing($post) ? 'amber' : 'green'">{{ $this->englishMissing($post) ? __('admin.common.translation_missing') : __('admin.common.translation_complete') }}</x-admin.badge></td>
                            <td><button type="button" role="switch" class="a-toggle" aria-checked="{{ $post->is_featured ? 'true' : 'false' }}" wire:click="toggle({{ $post->id }}, 'is_featured')" aria-label="{{ __('admin.content.featured') }}"></button></td>
                            <td>
                                <button type="button" role="switch" class="a-toggle" aria-checked="{{ $post->isPublished() || $post->is_scheduled ? 'true' : 'false' }}" wire:click="toggle({{ $post->id }}, 'published')" aria-label="{{ __('admin.content.published') }}"></button>
                                @if ($post->is_scheduled)<x-admin.badge color="violet" class="ms-1">{{ __('enums.publish_status.scheduled') }}</x-admin.badge>@endif
                            </td>
                            @include('livewire.admin.content.partials.row-actions', ['row' => $post, 'editUrl' => route('admin.posts.edit', $post->id), 'name' => $post->getTranslation('title', 'ar'), 'moduleName' => __('admin.content.singular.post')])
                        </tr>
                    @empty
                        <tr><td colspan="8"><x-admin.empty-state :title="__('admin.common.no_results')" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-admin.table.pagination :paginator="$rows" />
    </div>
</div>
