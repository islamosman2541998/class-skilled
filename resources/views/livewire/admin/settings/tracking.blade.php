<x-admin.settings-frame :group="$this->group()" :tabs="$this->tabs()">
    <x-admin.panel :title="__('admin.settings.sections.analytics')">
        <div class="grid gap-5 md:grid-cols-3">
            <x-admin.input model="data.ga4_id" :label="__('admin.settings.fields.ga4_id')" placeholder="G-XXXXXXX" dir="ltr" />
            <x-admin.input model="data.gtm_id" :label="__('admin.settings.fields.gtm_id')" placeholder="GTM-XXXXXX" dir="ltr" />
            <x-admin.input model="data.meta_pixel_id" :label="__('admin.settings.fields.meta_pixel_id')" dir="ltr" />
        </div>
        <p class="a-hint mt-3">{{ __('admin.settings.hints.tracking_production') }}</p>
    </x-admin.panel>

    <x-admin.panel :title="__('admin.settings.sections.custom_scripts')">
        <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-[13.5px] text-amber-900">{{ __('admin.settings.hints.scripts_warning') }}</div>
        <div class="grid gap-5 lg:grid-cols-2">
            <x-admin.textarea model="data.head_scripts" :label="__('admin.settings.fields.head_scripts')" rows="8" dir="ltr" class="font-mono" />
            <x-admin.textarea model="data.body_scripts" :label="__('admin.settings.fields.body_scripts')" rows="8" dir="ltr" class="font-mono" />
        </div>
    </x-admin.panel>
</x-admin.settings-frame>
