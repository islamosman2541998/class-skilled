{{-- List toolbar: search, module filters ($filtersView), featured / missing-English filters, clear, reorder hint. --}}
<div class="flex flex-wrap items-center gap-2 border-b border-a-border p-4">
    <x-admin.table.search :placeholder="$searchPlaceholder ?? __('admin.common.search')" />
    @isset($filtersView)
        @include($filtersView)
    @endisset
    @if ($featurable ?? false)
        <x-admin.table.filter key="featured" :label="__('admin.content.featured')" :options="['1' => __('admin.content.featured_only')]" :value="$filters['featured'] ?? null" />
    @endif
    <x-admin.table.filter key="missing" :label="__('admin.common.translation')" :options="['1' => __('admin.common.missing_english')]" :value="$filters['missing'] ?? null" />
    @if ($filters || $search)
        <button type="button" wire:click="clearFilters" class="text-[13.5px] font-bold text-[var(--c-deep)] hover:underline">{{ __('admin.common.clear_filters') }}</button>
    @endif
    @if ($this->canReorder())
        <span class="ms-auto text-[12.5px] text-[var(--a-muted)]">{{ __('admin.content.drag_hint') }}</span>
    @elseif (method_exists($this, 'sortable') && $this->sortable())
        <button type="button" class="ms-auto text-[12.5px] font-semibold text-[var(--a-muted)] hover:underline" wire:click="$set('sortField', 'sort'); $set('sortDirection', 'asc')">{{ __('admin.content.manual_order') }}</button>
    @endif
</div>
