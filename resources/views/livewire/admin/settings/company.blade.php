<x-admin.settings-frame :group="$this->group()" :tabs="$this->tabs()">
    <x-admin.panel :title="__('admin.settings.sections.legal')">
        <x-admin.locale-tabs :missing="$this->missingEnglish([$data['legal_name'], $data['licence_authority'], $data['classification'], $data['classification_authority']])">
            <x-slot:ar>
                <x-admin.input model="data.legal_name.ar" :label="__('admin.settings.fields.legal_name')" required />
                <x-admin.input model="data.licence_authority.ar" :label="__('admin.settings.fields.licence_authority')" />
                <x-admin.input model="data.classification.ar" :label="__('admin.settings.fields.classification')" />
                <x-admin.input model="data.classification_authority.ar" :label="__('admin.settings.fields.classification_authority')" />
            </x-slot:ar>
            <x-slot:en>
                <x-admin.input model="data.legal_name.en" :label="__('admin.settings.fields.legal_name')" />
                <x-admin.input model="data.licence_authority.en" :label="__('admin.settings.fields.licence_authority')" />
                <x-admin.input model="data.classification.en" :label="__('admin.settings.fields.classification')" />
                <x-admin.input model="data.classification_authority.en" :label="__('admin.settings.fields.classification_authority')" />
            </x-slot:en>
        </x-admin.locale-tabs>
    </x-admin.panel>

    <x-admin.panel :title="__('admin.settings.sections.licences')">
        <div class="grid gap-5 md:grid-cols-2">
            <x-admin.input model="data.trade_licence_no" :label="__('admin.settings.fields.trade_licence_no')" dir="ltr" required />
            <x-admin.date model="data.licence_expiry" :label="__('admin.settings.fields.licence_expiry')" />
            <x-admin.input model="data.classification_licence_no" :label="__('admin.settings.fields.classification_licence_no')" dir="ltr" />
            <x-admin.date model="data.classification_expiry" :label="__('admin.settings.fields.classification_expiry')" />
            <x-admin.input model="data.adcci_no" :label="__('admin.settings.fields.adcci_no')" dir="ltr" />
            <x-admin.input model="data.founded_year" type="number" :label="__('admin.settings.fields.founded_year')" />
        </div>
        <p class="a-hint mt-4">{{ __('admin.settings.hints.company') }}</p>
    </x-admin.panel>
</x-admin.settings-frame>
