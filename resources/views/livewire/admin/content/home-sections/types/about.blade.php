@include('livewire.admin.content.home-sections.types._heading', ['withLink' => true])
<x-admin.pair model="section.data.text" :label="__('admin.home.text')" textarea rows="4" />
<livewire:admin.asset-field wire:model="section.data.image_asset_ids" multiple kind="image" :label="__('admin.home.two_images')" wire:key="about-imgs-{{ $editing }}" :error="$errors->first('section.data.image_asset_ids')" />
<x-admin.repeater model="section.data.meta" :label="__('admin.home.meta')" :template="['label' => ['ar' => '', 'en' => ''], 'value' => ['ar' => '', 'en' => '']]">
    @foreach ($section['data']['meta'] ?? [] as $j => $item)
        <x-admin.repeater-item model="section.data.meta" :index="$j" :item="$item" :title="($item['label']['ar'] ?? '').' — '.($item['value']['ar'] ?? '')" :open="false">
            <x-admin.pair model="section.data.meta.{{ $j }}.label" :label="__('admin.home.label')" required />
            <x-admin.pair model="section.data.meta.{{ $j }}.value" :label="__('admin.home.value')" required />
        </x-admin.repeater-item>
    @endforeach
</x-admin.repeater>
