{{-- One row of the menu tree. Var: $row (MenuItem) --}}
<div @class(['flex items-center gap-3 rounded-xl border bg-white px-3 py-2.5', 'border-[var(--c-navy)] ring-2 ring-[var(--c-sky)]/30' => $editing === $row->id, 'border-a-border' => $editing !== $row->id, 'opacity-55' => ! $row->is_active])>
    <button type="button" data-sort-handle class="a-drag-handle" aria-label="{{ __('admin.common.drag') }}"><x-heroicon-m-bars-2 class="h-5 w-5" /></button>
    <button type="button" class="min-w-0 flex-1 text-start" wire:click="edit({{ $row->id }})">
        <span class="font-bold text-[var(--c-deep)]">{{ $this->itemLabel($row) }}</span>
        <span class="ms-2 text-[12.5px] text-[var(--a-muted)]">{{ $this->itemDescription($row) }}</span>
    </button>
    <x-admin.badge color="gray" class="hidden sm:inline-flex">{{ $row->type->label() }}</x-admin.badge>
    @if ($row->visibility->value !== 'all')
        <x-admin.badge color="amber" class="hidden sm:inline-flex">{{ $row->visibility->label() }}</x-admin.badge>
    @endif
    <button type="button" role="switch" class="a-toggle" aria-checked="{{ $row->is_active ? 'true' : 'false' }}" wire:click="toggleActive({{ $row->id }})" aria-label="{{ __('admin.common.active') }}"></button>
    <button type="button" class="a-link text-[13.5px]" wire:click="edit({{ $row->id }})">{{ __('admin.common.edit') }}</button>
    <button type="button" class="a-link-danger text-[13.5px]"
            @click="$confirm({ title: @js(__('admin.menus.delete_confirm')), text: @js(__('admin.menus.delete_text')), danger: true, confirmText: @js(__('admin.common.yes_delete')) }).then(ok => ok && $wire.delete({{ $row->id }}))">{{ __('admin.common.delete') }}</button>
</div>
