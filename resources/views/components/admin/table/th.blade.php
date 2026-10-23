{{--
    Table header cell; sortable when `field` is given (calls sortBy on the WithDataTable component).
    <x-admin.table.th field="name" :sort="$this->currentSort()">{{ __('…') }}</x-admin.table.th>
    `column` hides the cell with the column toggle (x-show="visible('…')").
--}}
@props(['field' => null, 'sort' => null, 'column' => null])
@php
    [$sortField, $sortDirection] = $sort ?? [null, null];
    $active = $field && $sortField === $field;
@endphp
<th {{ $attributes }} @if ($column) x-show="visible('{{ $column }}')" @endif
    @if ($active) aria-sort="{{ $sortDirection === 'asc' ? 'ascending' : 'descending' }}" @endif>
    @if ($field)
        <button type="button" wire:click="sortBy('{{ $field }}')" class="inline-flex items-center gap-1 font-semibold hover:text-[var(--c-deep)]">
            {{ $slot }}
            @if ($active)
                @if ($sortDirection === 'asc')
                    <x-heroicon-m-chevron-up class="h-3.5 w-3.5" />
                @else
                    <x-heroicon-m-chevron-down class="h-3.5 w-3.5" />
                @endif
            @else
                <x-heroicon-m-chevron-up-down class="h-3.5 w-3.5 opacity-40" />
            @endif
        </button>
    @else
        {{ $slot }}
    @endif
</th>
