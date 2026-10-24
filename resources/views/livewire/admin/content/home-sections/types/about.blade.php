@include('livewire.admin.content.home-sections.types._heading', ['withLink' => true])
<x-admin.pair model="section.data.text" :label="__('admin.home.text')" textarea rows="5" />
<livewire:admin.asset-field wire:model="section.data.image_asset_id" kind="image" :label="__('admin.home.about_image')" wire:key="about-img-{{ $editing }}" :error="$errors->first('section.data.image_asset_id')" />
