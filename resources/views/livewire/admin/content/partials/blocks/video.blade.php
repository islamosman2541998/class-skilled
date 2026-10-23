<x-admin.pair :model="$p.'.title'" :label="__('admin.blocks.fields.title')" />
<livewire:admin.asset-field wire:model="{{ $p }}.asset_id" :label="__('admin.blocks.fields.video')" :hint="__('admin.blocks.fields.video_hint')" wire:key="vid-{{ $block['id'] }}-{{ $i }}" :error="$errors->first($p.'.asset_id')" />
