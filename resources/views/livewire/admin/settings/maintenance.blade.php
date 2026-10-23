<x-admin.settings-frame :group="$this->group()" :tabs="$this->tabs()">
    <x-admin.panel :title="__('admin.settings.sections.maintenance')">
        <x-admin.toggle model="data.enabled" :label="__('admin.settings.fields.maintenance_enabled')" :hint="__('admin.settings.hints.maintenance')" class="mb-6" />
        <x-admin.locale-tabs :missing="$this->missingEnglish([$data['message']])">
            <x-slot:ar><x-admin.textarea model="data.message.ar" :label="__('admin.settings.fields.maintenance_message')" rows="3" /></x-slot:ar>
            <x-slot:en><x-admin.textarea model="data.message.en" :label="__('admin.settings.fields.maintenance_message')" rows="3" /></x-slot:en>
        </x-admin.locale-tabs>
    </x-admin.panel>

    <x-admin.panel :title="__('admin.settings.fields.allowed_ips')">
        <x-slot:actions>
            <button type="button" class="a-btn a-btn-secondary a-btn-sm" wire:click="addCurrentIp">{{ __('admin.settings.add_my_ip', ['ip' => request()->ip()]) }}</button>
        </x-slot:actions>
        <x-admin.repeater model="data.allowed_ips" :template="['value' => '']">
            @foreach ($data['allowed_ips'] as $i => $item)
                <x-admin.repeater-item model="data.allowed_ips" :index="$i" :item="$item" :title="$item['value'] ?? ''">
                    <x-admin.input model="data.allowed_ips.{{ $i }}.value" label="IP" dir="ltr" />
                </x-admin.repeater-item>
            @endforeach
        </x-admin.repeater>
    </x-admin.panel>
</x-admin.settings-frame>
