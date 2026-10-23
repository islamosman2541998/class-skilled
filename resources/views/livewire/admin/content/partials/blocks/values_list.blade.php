<x-admin.pair :model="$p.'.title'" :label="__('admin.blocks.fields.title')" />
<x-admin.repeater :model="$p.'.items'" :label="__('admin.blocks.fields.items')" :template="['title' => ['ar' => '', 'en' => ''], 'text' => ['ar' => '', 'en' => '']]">
    @foreach ($block['data']['items'] ?? [] as $j => $item)
        <x-admin.repeater-item :model="$p.'.items'" :index="$j" :item="$item" :title="$item['title']['ar'] ?? ''">
            <x-admin.pair :model="$p.'.items.'.$j.'.title'" :label="__('admin.blocks.fields.title')" required />
            <x-admin.pair :model="$p.'.items.'.$j.'.text'" :label="__('admin.blocks.fields.text')" textarea />
        </x-admin.repeater-item>
    @endforeach
</x-admin.repeater>
