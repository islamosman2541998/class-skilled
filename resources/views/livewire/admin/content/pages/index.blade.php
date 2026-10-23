<div>
    <x-admin.page-header :title="__('admin.nav.pages')" :subtitle="__('admin.pages.subtitle')" :breadcrumbs="[__('admin.nav.content') => null, __('admin.nav.pages') => null]">
        <x-slot:actions>
            <button type="button" wire:click="export" class="a-btn a-btn-secondary">{{ __('admin.common.export') }}</button>
            @can('create', \App\Models\Page::class)
                <a href="{{ route('admin.pages.create') }}" wire:navigate class="a-btn a-btn-primary">{{ __('admin.pages.add') }}</a>
            @endcan
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.table.status-tabs :tabs="$this->statusTabs()" :value="$filters['status'] ?? ''" />

    @php($rows = $this->rows)
    @php($sort = $this->currentSort())
    <div class="a-panel">
        @include('livewire.admin.content.partials.list-toolbar')
        @include('livewire.admin.content.partials.bulk', ['actions' => ['publish', 'unpublish']])
        <div class="overflow-x-auto">
            <table class="a-table">
                <thead>
                    <tr>
                        <th class="w-10"><input type="checkbox" class="a-check" aria-label="{{ __('admin.common.select_page') }}" wire:click="{{ count($selected) && count($selected) >= $rows->count() ? 'clearSelection' : 'selectPage' }}" @checked($rows->count() && count($selected) >= $rows->count())></th>
                        <th class="w-8"></th>
                        <th>{{ __('admin.content.fields.title') }}</th>
                        <th>{{ __('admin.common.translation') }}</th>
                        <th>{{ __('admin.pages.menus') }}</th>
                        <th>{{ __('admin.content.published') }}</th>
                        <x-admin.table.th field="updated" :sort="$sort">{{ __('admin.common.updated_at') }}</x-admin.table.th>
                        <th></th>
                    </tr>
                </thead>
                <tbody @if ($this->canReorder()) x-sortable.handle="reorder" @endif wire:loading.class="opacity-60">
                    @forelse ($rows as $page)
                        <tr wire:key="page-{{ $page->id }}" data-sort-id="{{ $page->id }}">
                            <td><input type="checkbox" class="a-check" value="{{ $page->id }}" wire:model.live="selected" aria-label="{{ $page->getTranslation('title', 'ar') }}"></td>
                            <td>@if ($this->canReorder())<button type="button" data-sort-handle class="a-drag-handle" aria-label="{{ __('admin.common.drag') }}"><x-heroicon-m-bars-2 class="h-4 w-4" /></button>@endif</td>
                            <td>
                                <a href="{{ route('admin.pages.edit', $page->id) }}" wire:navigate class="font-bold text-[var(--c-deep)] hover:underline">{{ $page->getTranslation('title', app()->getLocale(), false) ?: $page->getTranslation('title', 'ar') }}</a>
                                <p class="text-[12.5px] text-[var(--a-muted)]" dir="ltr">/{{ $page->getTranslation('slug', 'ar', false) }}@if ($page->parent) · {{ __('admin.pages.child_of', ['name' => $page->parent->getTranslation('title', 'ar')]) }}@endif</p>
                            </td>
                            <td><x-admin.badge :color="$this->englishMissing($page) ? 'amber' : 'green'">{{ $this->englishMissing($page) ? __('admin.common.translation_missing') : __('admin.common.translation_complete') }}</x-admin.badge></td>
                            <td class="text-[13px]">{{ ($inMenus[$page->id] ?? 0) ? __('admin.pages.in_menus', ['count' => $inMenus[$page->id]]) : '—' }}</td>
                            <td>
                                <button type="button" role="switch" class="a-toggle" aria-checked="{{ $page->isPublished() || $page->is_scheduled ? 'true' : 'false' }}" wire:click="toggle({{ $page->id }}, 'published')" aria-label="{{ __('admin.content.published') }}"></button>
                                @if ($page->is_scheduled)<x-admin.badge color="violet" class="ms-2">{{ __('enums.publish_status.scheduled') }}</x-admin.badge>@endif
                            </td>
                            <td class="text-[13px] text-[var(--a-muted)]">{{ $page->updated_at?->diffForHumans() }}</td>
                            @include('livewire.admin.content.partials.row-actions', ['row' => $page, 'editUrl' => route('admin.pages.edit', $page->id), 'name' => $page->getTranslation('title', 'ar'), 'moduleName' => __('admin.content.singular.page')])
                        </tr>
                    @empty
                        <tr><td colspan="8"><x-admin.empty-state :title="__('admin.common.no_results')">@can('create', \App\Models\Page::class)<a href="{{ route('admin.pages.create') }}" wire:navigate class="a-btn a-btn-primary">{{ __('admin.pages.add') }}</a>@endcan</x-admin.empty-state></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-admin.table.pagination :paginator="$rows" />
    </div>
</div>
