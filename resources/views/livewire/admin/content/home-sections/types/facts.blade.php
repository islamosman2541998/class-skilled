<button type="button" class="a-btn a-btn-secondary a-btn-sm" wire:click="fillFacts">{{ __('admin.home.fill_from_company') }}</button>
<x-admin.repeater model="section.data.items" :label="__('admin.home.facts')" :template="['label' => ['ar' => '', 'en' => ''], 'value' => ['ar' => '', 'en' => '']]">
    @foreach ($section['data']['items'] ?? [] as $j => $item)
        <x-admin.repeater-item model="section.data.items" :index="$j" :item="$item" :title="($item['label']['ar'] ?? '').' — '.($item['value']['ar'] ?? '')" :open="false">
            <x-admin.pair model="section.data.items.{{ $j }}.label" :label="__('admin.home.label')" required />
            <x-admin.pair model="section.data.items.{{ $j }}.value" :label="__('admin.home.value')" required />
        </x-admin.repeater-item>
    @endforeach
</x-admin.repeater>
