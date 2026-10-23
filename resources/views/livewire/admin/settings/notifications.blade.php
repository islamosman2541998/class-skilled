<x-admin.settings-frame :group="$this->group()" :tabs="$this->tabs()">
    <x-admin.panel :title="__('admin.settings.sections.recipients')" :subtitle="__('admin.settings.hints.recipients')">
        <x-slot:actions>
            <button type="button" class="a-btn a-btn-secondary a-btn-sm" wire:click="sendTest">{{ __('admin.settings.send_test') }}</button>
        </x-slot:actions>
        <div class="grid gap-6 lg:grid-cols-3">
            @foreach (['quote_emails', 'contact_emails', 'careers_emails'] as $key)
                <x-admin.repeater model="data.{{ $key }}" :label="__('admin.settings.fields.'.$key)" :template="['value' => '']" :add-label="__('admin.settings.fields.add_email')">
                    @foreach ($data[$key] as $i => $item)
                        <x-admin.repeater-item model="data.{{ $key }}" :index="$i" :item="$item" :title="$item['value'] ?? ''">
                            <x-admin.input model="data.{{ $key }}.{{ $i }}.value" type="email" :label="__('admin.settings.fields.email')" />
                        </x-admin.repeater-item>
                    @endforeach
                </x-admin.repeater>
            @endforeach
        </div>
        <x-admin.input model="data.from_name" :label="__('admin.settings.fields.from_name')" class="mt-6 max-w-md" />
    </x-admin.panel>

    <x-admin.panel :title="__('admin.settings.sections.autoreply')">
        <x-admin.toggle model="data.autoreply_enabled" :label="__('admin.settings.fields.autoreply_enabled')" :hint="__('admin.settings.hints.autoreply')" class="mb-5" />
        <x-admin.locale-tabs :missing="$this->missingEnglish([$data['autoreply_subject'], $data['autoreply_body']])">
            <x-slot:ar>
                <x-admin.input model="data.autoreply_subject.ar" :label="__('admin.settings.fields.autoreply_subject')" />
                <x-admin.textarea model="data.autoreply_body.ar" :label="__('admin.settings.fields.autoreply_body')" rows="6" />
            </x-slot:ar>
            <x-slot:en>
                <x-admin.input model="data.autoreply_subject.en" :label="__('admin.settings.fields.autoreply_subject')" />
                <x-admin.textarea model="data.autoreply_body.en" :label="__('admin.settings.fields.autoreply_body')" rows="6" />
            </x-slot:en>
        </x-admin.locale-tabs>
        <p class="a-hint">{{ __('admin.settings.hints.placeholders') }} <code dir="ltr">{name}</code> <code dir="ltr">{number}</code></p>
    </x-admin.panel>
</x-admin.settings-frame>
