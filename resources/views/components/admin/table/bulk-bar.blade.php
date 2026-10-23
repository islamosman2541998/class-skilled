{{--
    Bulk actions bar, shown when rows are selected (WithDataTable).
    <x-admin.table.bulk-bar :count="$this->selectedCount()" :total="$this->rows->total()" :all="$selectAllMatching">
        <button class="a-btn a-btn-secondary a-btn-sm" wire:click="…">…</button>
    </x-admin.table.bulk-bar>
--}}
@props(['count' => 0, 'total' => 0, 'all' => false])
@if ($count > 0)
    <div {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-3 border-b border-a-border bg-[#eef1f6] px-4 py-3']) }} role="region" aria-label="{{ __('admin.table.bulk_actions') }}">
        <span class="text-[13.5px] font-semibold">{{ __('admin.common.selected', ['count' => $count]) }}</span>
        @if (! $all && $total > $count)
            <button type="button" wire:click="selectAll" class="text-[13px] font-bold text-[var(--c-navy)] underline">{{ __('admin.common.select_all_matching', ['count' => $total]) }}</button>
        @endif
        <div class="flex flex-wrap items-center gap-2">{{ $slot }}</div>
        <button type="button" wire:click="clearSelection" class="ms-auto text-[13px] font-semibold text-[var(--a-muted)] hover:underline">{{ __('admin.common.deselect') }}</button>
    </div>
@endif
