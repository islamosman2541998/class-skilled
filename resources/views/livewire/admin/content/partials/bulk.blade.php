{{-- Bulk actions bar for content lists. Vars: $rows, $actions (list of bulk action keys) --}}
<x-admin.table.bulk-bar :count="$this->selectedCount()" :total="$rows->total()" :all="$selectAllMatching">
    @foreach ($actions as $action)
        <button type="button" class="a-btn a-btn-secondary a-btn-sm" wire:click="bulk('{{ $action }}')">{{ __('admin.content.bulk.'.$action) }}</button>
    @endforeach
    <button type="button" class="a-btn a-btn-secondary a-btn-sm" wire:click="exportSelected">{{ __('admin.common.export_selected') }}</button>
    <button type="button" class="a-link-danger px-2 text-[13.5px]"
            @click="$confirm({ title: @js(__('admin.common.confirm_bulk_delete_title')), text: @js(__('admin.common.confirm_bulk_delete_text', ['count' => $this->selectedCount()])), danger: true, confirmText: @js(__('admin.common.yes_delete')) }).then(ok => ok && $wire.bulk('delete'))">{{ __('admin.common.delete') }}</button>
</x-admin.table.bulk-bar>
