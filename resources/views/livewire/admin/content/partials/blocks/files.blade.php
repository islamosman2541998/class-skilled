<x-admin.pair :model="$p.'.title'" :label="__('admin.blocks.fields.title')" />
<livewire:admin.asset-field wire:model="{{ $p }}.asset_ids" multiple kind="document" :label="__('admin.blocks.fields.files')" wire:key="files-{{ $block['id'] }}-{{ $i }}" :error="$errors->first($p.'.asset_ids')" />
