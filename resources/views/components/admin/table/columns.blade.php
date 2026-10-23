{{--
    Column visibility menu. Must be inside an element with x-data="columnToggle('table-key', {...})".
    <x-admin.table.columns :columns="['email' => __('admin.users.email')]" />
--}}
@props(['columns' => []])
<div x-data="{ open: false }" class="relative" @click.outside="open = false">
    <button type="button" class="a-chip" @click="open = ! open" :aria-expanded="open">
        {{ __('admin.common.columns') }} <x-heroicon-m-chevron-down class="h-4 w-4 text-[var(--a-muted)]" />
    </button>
    <div x-cloak x-show="open" x-transition class="a-popover end-0 top-11">
        @foreach ($columns as $key => $label)
            <label class="a-menu-item cursor-pointer">
                <input type="checkbox" class="a-check" :checked="visible('{{ $key }}')" @change="toggle('{{ $key }}')">
                {{ $label }}
            </label>
        @endforeach
    </div>
</div>
