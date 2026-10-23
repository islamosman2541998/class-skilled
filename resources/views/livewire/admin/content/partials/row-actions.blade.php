{{-- Row actions: edit link + delete with confirm. Vars: $row, $editUrl, $name --}}
<td class="whitespace-nowrap text-end">
    @can('update', $row)
        <a href="{{ $editUrl }}" wire:navigate class="a-link me-3">{{ __('admin.common.edit') }}</a>
    @endcan
    @can('delete', $row)
        <button type="button" class="a-link-danger"
                @click="$confirm({ title: @js(__('admin.content.delete_title', ['module' => $moduleName ?? ''])), text: @js(__('admin.common.confirm_delete_text', ['name' => $name])), danger: true, confirmText: @js(__('admin.common.yes_delete')) }).then(ok => ok && $wire.delete({{ $row->id }}))">
            {{ __('admin.common.delete') }}
        </button>
    @endcan
</td>
