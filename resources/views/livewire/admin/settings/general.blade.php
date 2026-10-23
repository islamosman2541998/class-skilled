<x-admin.settings-frame :group="$this->group()" :tabs="$this->tabs()">
    <x-admin.panel :title="__('admin.settings.sections.identity')">
        <x-admin.locale-tabs :missing="$this->missingEnglish([$data['site_name'], $data['tagline'], $data['footer_about']])">
            <x-slot:ar>
                <x-admin.input model="data.site_name.ar" :label="__('admin.settings.fields.site_name')" required />
                <x-admin.input model="data.tagline.ar" :label="__('admin.settings.fields.tagline')" />
                <x-admin.textarea model="data.footer_about.ar" :label="__('admin.settings.fields.footer_about')" rows="3" :max="300" />
            </x-slot:ar>
            <x-slot:en>
                <x-admin.input model="data.site_name.en" :label="__('admin.settings.fields.site_name')" />
                <x-admin.input model="data.tagline.en" :label="__('admin.settings.fields.tagline')" />
                <x-admin.textarea model="data.footer_about.en" :label="__('admin.settings.fields.footer_about')" rows="3" :max="300" />
            </x-slot:en>
        </x-admin.locale-tabs>
    </x-admin.panel>

    <x-admin.panel :title="__('admin.settings.sections.logos')">
        <div class="grid gap-6 md:grid-cols-3">
            <livewire:admin.asset-field wire:model="data.logo_asset_id" kind="image" :label="__('admin.settings.fields.logo')" :hint="__('admin.settings.hints.logo')" :error="$errors->first('data.logo_asset_id')" />
            <livewire:admin.asset-field wire:model="data.logo_white_asset_id" kind="image" :label="__('admin.settings.fields.logo_white')" :error="$errors->first('data.logo_white_asset_id')" />
            <livewire:admin.asset-field wire:model="data.favicon_asset_id" kind="image" :label="__('admin.settings.fields.favicon')" :hint="__('admin.settings.hints.favicon')" :error="$errors->first('data.favicon_asset_id')" />
        </div>
    </x-admin.panel>

    <x-admin.panel :title="__('admin.settings.sections.language')">
        <x-admin.segmented model="data.default_locale" :label="__('admin.settings.fields.default_locale')" :options="['ar' => 'العربية', 'en' => 'English']" />
        <p class="a-hint">{{ __('admin.settings.hints.default_locale') }}</p>
    </x-admin.panel>
</x-admin.settings-frame>
