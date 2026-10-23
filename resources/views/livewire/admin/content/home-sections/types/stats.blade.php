@include('livewire.admin.content.home-sections.types._heading')
<x-admin.toggle model="section.data.count_up" :label="__('admin.home.count_up')" />
<x-admin.repeater model="section.data.items" :label="__('admin.home.items')" :template="['value' => '', 'suffix' => ['ar' => '', 'en' => ''], 'label' => ['ar' => '', 'en' => '']]">
    @foreach ($section['data']['items'] ?? [] as $j => $item)
        <x-admin.repeater-item model="section.data.items" :index="$j" :item="$item" :title="($item['value'] ?? '').' '.($item['label']['ar'] ?? '')" :open="false">
            <x-admin.input model="section.data.items.{{ $j }}.value" :label="__('admin.home.value')" dir="ltr" required />
            <x-admin.pair model="section.data.items.{{ $j }}.suffix" :label="__('admin.home.suffix')" />
            <x-admin.pair model="section.data.items.{{ $j }}.label" :label="__('admin.home.label')" required />
        </x-admin.repeater-item>
    @endforeach
</x-admin.repeater>
