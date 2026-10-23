{{--
    Filter chip with a dropdown of options, bound to `filters.{key}` (WithDataTable).
    Shows "Label: value ×" when active.
    <x-admin.table.filter key="role" :label="__('admin.users.role')" :options="['admin' => 'مدير']" :value="$filters['role'] ?? null" />
--}}
@props(['key', 'label', 'options' => [], 'value' => null])
<div x-data="{ open: false }" class="relative" @click.outside="open = false" @keydown.escape="open = false">
    @if (filled($value) && isset($options[$value]))
        <span class="a-chip" aria-pressed="true">
            <button type="button" @click="open = ! open" class="flex items-center gap-1.5">
                <span class="text-[var(--a-muted)]">{{ $label }}:</span>
                <span class="font-extrabold">{{ $options[$value] }}</span>
            </button>
            <button type="button" wire:click="setFilter('{{ $key }}', null)" class="rounded p-0.5 hover:bg-white" aria-label="{{ __('admin.common.remove') }} {{ $label }}">
                <x-heroicon-m-x-mark class="h-3.5 w-3.5" />
            </button>
        </span>
    @else
        <button type="button" class="a-chip" @click="open = ! open" :aria-expanded="open">
            {{ $label }} <x-heroicon-m-chevron-down class="h-4 w-4 text-[var(--a-muted)]" />
        </button>
    @endif
    <div x-cloak x-show="open" x-transition class="a-popover start-0 top-11 max-h-72 overflow-y-auto">
        @foreach ($options as $optionValue => $optionLabel)
            <button type="button" class="a-menu-item" wire:click="setFilter('{{ $key }}', @js((string) $optionValue))" @click="open = false">
                @if ((string) $value === (string) $optionValue)<x-heroicon-m-check class="h-4 w-4 text-[var(--c-navy)]" />@else<span class="w-4"></span>@endif
                {{ $optionLabel }}
            </button>
        @endforeach
    </div>
</div>
