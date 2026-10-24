<div x-data="dirtyGuard(@js(__('admin.common.unsaved_changes')))">
    <x-admin.page-header :title="__('admin.nav.seo_settings')" :subtitle="__('admin.seo.subtitle')"
        :breadcrumbs="[__('admin.nav.seo') => null, __('admin.nav.seo_settings') => null]">
        <x-slot:actions>
            <a href="{{ url('/robots.txt') }}" target="_blank" rel="noopener" class="a-btn a-btn-secondary">robots.txt</a>
            <a href="{{ url('/sitemap.xml') }}" target="_blank" rel="noopener" class="a-btn a-btn-secondary">sitemap.xml</a>
            <button type="button" wire:click="regenerateSitemap" wire:loading.attr="disabled" wire:target="regenerateSitemap" class="a-btn a-btn-secondary">{{ __('admin.seo.regenerate_sitemap') }}</button>
            <button type="submit" form="settings-form" class="a-btn a-btn-primary">{{ __('admin.settings.save') }}</button>
        </x-slot:actions>
    </x-admin.page-header>

    <form id="settings-form" wire:submit="save" class="grid gap-6 xl:grid-cols-2">
        @include('livewire.admin.seo.partials.search-appearance')

        <x-admin.panel :title="__('admin.seo.defaults')">
            <x-admin.locale-tabs :missing="$this->missingEnglish([$data['title_suffix'], $data['default_description']])">
                <x-slot:ar>
                    <x-admin.input model="data.title_suffix.ar" :label="__('admin.seo.title_suffix')" :hint="__('admin.seo.title_suffix_hint')" />
                    <x-admin.textarea model="data.default_description.ar" :label="__('admin.seo.default_description')" :max="160" rows="3" />
                </x-slot:ar>
                <x-slot:en>
                    <x-admin.input model="data.title_suffix.en" :label="__('admin.seo.title_suffix')" />
                    <x-admin.textarea model="data.default_description.en" :label="__('admin.seo.default_description')" :max="160" rows="3" />
                </x-slot:en>
            </x-admin.locale-tabs>
            <div class="mt-6">
                <livewire:admin.asset-field wire:model="data.default_og_asset_id" kind="image" :label="__('admin.seo.default_og')" :hint="__('admin.seo.default_og_hint')" :error="$errors->first('data.default_og_asset_id')" />
            </div>
        </x-admin.panel>

        <div class="space-y-6">
            <x-admin.panel :title="__('admin.seo.verification')">
                <div class="space-y-4">
                    <x-admin.input model="data.google_verification" :label="__('admin.seo.google_verification')" dir="ltr" />
                    <x-admin.input model="data.bing_verification" :label="__('admin.seo.bing_verification')" dir="ltr" />
                </div>
            </x-admin.panel>

            <x-admin.panel title="robots.txt" :subtitle="__('admin.seo.robots_hint')">
                <x-admin.textarea model="data.robots_txt" rows="8" dir="ltr" class="font-mono" />
            </x-admin.panel>

            <x-admin.panel :title="__('admin.seo.schema_extra')" :subtitle="__('admin.seo.schema_hint')">
                <x-admin.textarea model="schemaJson" rows="6" dir="ltr" class="font-mono" placeholder='{"foundingDate": "2025-05-12"}' />
            </x-admin.panel>
        </div>
    </form>
</div>
