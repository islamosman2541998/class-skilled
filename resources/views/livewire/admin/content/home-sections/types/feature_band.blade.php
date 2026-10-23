@include('livewire.admin.content.home-sections.types._heading', ['withLink' => true])
<x-admin.repeater model="section.data.bullets" :label="__('admin.home.bullets')" :template="['ar' => '', 'en' => '']">
    @foreach ($section['data']['bullets'] ?? [] as $j => $bullet)
        <x-admin.repeater-item model="section.data.bullets" :index="$j" :item="$bullet" :title="$bullet['ar'] ?? ''" :open="false">
            <x-admin.pair model="section.data.bullets.{{ $j }}" required />
        </x-admin.repeater-item>
    @endforeach
</x-admin.repeater>
<livewire:admin.asset-field wire:model="section.data.image_asset_ids" multiple kind="image" :label="__('admin.home.three_images')" wire:key="fb-imgs-{{ $editing }}" :error="$errors->first('section.data.image_asset_ids')" />
