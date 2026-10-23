<x-admin.pair :model="$p.'.title'" :label="__('admin.blocks.fields.title')" />
<x-admin.repeater :model="$p.'.items'" :label="__('admin.blocks.fields.items')" :template="['title' => ['ar' => '', 'en' => ''], 'text' => ['ar' => '', 'en' => ''], 'asset_id' => null]">
    @foreach ($block['data']['items'] ?? [] as $j => $item)
        <x-admin.repeater-item :model="$p.'.items'" :index="$j" :item="$item" :title="$item['title']['ar'] ?? ''">
            <div class="grid gap-4 lg:grid-cols-[180px_minmax(0,1fr)]">
                <livewire:admin.asset-field wire:model="{{ $p }}.items.{{ $j }}.asset_id" kind="image" :label="__('admin.blocks.fields.image')" wire:key="card-{{ $block['id'] }}-{{ $i }}-{{ $item['_key'] ?? $j }}" />
                <div class="space-y-4">
                    <x-admin.pair :model="$p.'.items.'.$j.'.title'" :label="__('admin.blocks.fields.title')" required />
                    <x-admin.pair :model="$p.'.items.'.$j.'.text'" :label="__('admin.blocks.fields.text')" textarea />
                </div>
            </div>
        </x-admin.repeater-item>
    @endforeach
</x-admin.repeater>
