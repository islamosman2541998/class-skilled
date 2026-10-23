@include('livewire.admin.content.home-sections.types._heading')
<x-admin.repeater model="section.data.items" :label="__('admin.home.items')" :template="['title' => ['ar' => '', 'en' => ''], 'text' => ['ar' => '', 'en' => '']]">
    @foreach ($section['data']['items'] ?? [] as $j => $item)
        <x-admin.repeater-item model="section.data.items" :index="$j" :item="$item" :title="$item['title']['ar'] ?? ''" :open="false">
            <x-admin.pair model="section.data.items.{{ $j }}.title" :label="__('admin.blocks.fields.title')" required />
            <x-admin.pair model="section.data.items.{{ $j }}.text" :label="__('admin.blocks.fields.text')" textarea />
        </x-admin.repeater-item>
    @endforeach
</x-admin.repeater>
