{{--
    One item of <x-admin.repeater>. Items need a stable `_key` (added by addRepeaterItem).
    Props: model (repeater path), index, item (the item array), title (summary shown when collapsed), numbered.
--}}
@props(['model', 'index', 'item' => [], 'title' => null, 'numbered' => false, 'open' => true])
@php($key = $item['_key'] ?? $index)
<div wire:key="{{ $model }}-{{ $key }}" data-sort-id="{{ $key }}" x-data="{ open: @js($open) }" class="rounded-xl border border-a-border bg-white">
    <div class="flex items-center gap-2 px-3 py-2">
        <button type="button" data-sort-handle class="a-drag-handle rounded p-1" aria-label="{{ __('admin.common.drag') }}">
            <x-heroicon-m-bars-2 class="h-4 w-4" />
        </button>
        @if ($numbered)
            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[var(--c-sky)] text-[12px] font-extrabold text-[var(--c-deep)]">{{ $index + 1 }}</span>
        @endif
        <button type="button" class="min-w-0 flex-1 truncate text-start text-[14px] font-semibold" @click="open = ! open" :aria-expanded="open">
            {{ filled($title) ? $title : '#'.($index + 1) }}
        </button>
        <button type="button" class="rounded p-1.5 text-[var(--a-muted)] hover:bg-a-bg" @click="open = ! open" :aria-label="open ? @js(__('admin.common.collapse')) : @js(__('admin.common.expand'))">
            <x-heroicon-m-chevron-down class="h-4 w-4 transition-transform" x-bind:class="open ? 'rotate-180' : ''" />
        </button>
        <button type="button" class="rounded p-1.5 text-[var(--a-danger)] hover:bg-red-50" wire:click="removeRepeaterItem('{{ $model }}', {{ $index }})" aria-label="{{ __('admin.common.remove') }}">
            <x-heroicon-m-trash class="h-4 w-4" />
        </button>
    </div>
    <div x-show="open" x-collapse class="space-y-4 border-t border-a-border px-4 py-4">
        {{ $slot }}
    </div>
</div>
