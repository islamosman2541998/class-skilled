{{--
    Debounced search box bound to `search` (WithDataTable).
    <x-admin.table.search :placeholder="__('…')" />
--}}
@props(['placeholder' => null])
<div {{ $attributes->merge(['class' => 'relative min-w-[220px] flex-1']) }}>
    <x-heroicon-o-magnifying-glass class="pointer-events-none absolute inset-y-0 start-3 my-auto h-4 w-4 text-[var(--a-muted)]" />
    <input type="search" wire:model.live.debounce.300ms="search" class="a-input ps-9" placeholder="{{ $placeholder ?? __('admin.common.search') }}" aria-label="{{ $placeholder ?? __('admin.common.search') }}">
</div>
