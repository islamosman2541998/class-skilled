<x-admin.pair :model="$p.'.title'" :label="__('admin.blocks.fields.title')" />
<x-admin.segmented :model="$p.'.columns'" :label="__('admin.blocks.fields.columns')" :options="[2 => '2', 3 => '3', 4 => '4']" />
<livewire:admin.asset-field wire:model="{{ $p }}.asset_ids" multiple kind="image" :label="__('admin.blocks.fields.images')" wire:key="gal-{{ $block['id'] }}-{{ $i }}" :error="$errors->first($p.'.asset_ids')" />
