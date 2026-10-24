{{-- How the site appears in Google results, with a live preview per language. --}}
@php
    $iconAsset = $data['search_icon_asset_id'] ? \App\Models\Asset::query()->find($data['search_icon_asset_id']) : null;
    $iconUrl = $iconAsset?->url('thumb') ?? (\App\Services\FaviconGenerator::urls()[48] ?? asset('images/brand/mark-sm.png'));
@endphp
<x-admin.panel :title="__('admin.seo.search.title')" :subtitle="__('admin.seo.search.subtitle')" class="xl:col-span-2">
    <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
        <div class="space-y-5">
            <x-admin.locale-tabs :missing="$this->missingEnglish([$data['search_site_name'], $data['home_title'], $data['home_description']])">
                <x-slot:ar>
                    <x-admin.input model="data.search_site_name.ar" :label="__('admin.seo.search.site_name')" :hint="__('admin.seo.search.site_name_hint')" required live />
                    <x-admin.input model="data.search_alternate_name.ar" :label="__('admin.seo.search.alternate_name')" :hint="__('admin.seo.search.alternate_name_hint')" />
                    <x-admin.input model="data.home_title.ar" :label="__('admin.seo.search.home_title')" :hint="__('admin.seo.search.home_title_hint')" required live />
                    <x-admin.textarea model="data.home_description.ar" :label="__('admin.seo.search.home_description')" :hint="__('admin.seo.search.home_description_hint')" :max="160" rows="3" required live />
                </x-slot:ar>
                <x-slot:en>
                    <x-admin.input model="data.search_site_name.en" :label="__('admin.seo.search.site_name')" dir="ltr" live />
                    <x-admin.input model="data.search_alternate_name.en" :label="__('admin.seo.search.alternate_name')" dir="ltr" />
                    <x-admin.input model="data.home_title.en" :label="__('admin.seo.search.home_title')" dir="ltr" live />
                    <x-admin.textarea model="data.home_description.en" :label="__('admin.seo.search.home_description')" :max="160" rows="3" dir="ltr" live />
                </x-slot:en>
            </x-admin.locale-tabs>

            <div class="grid gap-5 sm:grid-cols-2">
                <livewire:admin.asset-field wire:model.live="data.search_icon_asset_id" kind="image" :label="__('admin.seo.search.icon')" :hint="__('admin.seo.search.icon_hint')" :error="$errors->first('data.search_icon_asset_id')" />
                <livewire:admin.asset-field wire:model="data.search_logo_asset_id" kind="image" :label="__('admin.seo.search.logo')" :hint="__('admin.seo.search.logo_hint')" :error="$errors->first('data.search_logo_asset_id')" />
            </div>
        </div>

        <div class="space-y-4">
            <p class="text-[13px] font-bold text-[var(--a-muted)]">{{ __('admin.seo.search.preview') }}</p>
            @foreach (['ar' => url('/'), 'en' => url('/en')] as $previewLocale => $previewUrl)
                <div class="rounded-2xl border border-a-border bg-white p-5 shadow-[0_10px_30px_-24px_rgb(27_42_71/0.6)]" dir="{{ $previewLocale === 'ar' ? 'rtl' : 'ltr' }}" lang="{{ $previewLocale }}">
                    <div class="flex items-center gap-3">
                        <span class="grid h-7 w-7 shrink-0 place-items-center overflow-hidden rounded-full border border-[#dadce0] bg-white">
                            <img src="{{ $iconUrl }}" alt="" class="h-[18px] w-[18px] object-contain">
                        </span>
                        <span class="min-w-0 leading-tight" style="font-family: {{ $previewLocale === 'ar' ? 'Tahoma, Arial, sans-serif' : 'Arial, sans-serif' }}">
                            <span class="block truncate text-[14px] text-[#202124]">{{ ($data['search_site_name'][$previewLocale] ?? '') ?: ($data['search_site_name']['ar'] ?? '') }}</span>
                            <span class="block truncate text-[12px] text-[#4d5156]" dir="ltr">{{ preg_replace('~^https?://~', '', $previewUrl) }}</span>
                        </span>
                    </div>
                    <p class="mt-2 line-clamp-1 text-[20px] leading-snug text-[#1a0dab]" style="font-family: {{ $previewLocale === 'ar' ? 'Tahoma, Arial, sans-serif' : 'Arial, sans-serif' }}">{{ ($data['home_title'][$previewLocale] ?? '') ?: ($data['home_title']['ar'] ?? '') }}</p>
                    <p class="mt-1 line-clamp-2 text-[14px] leading-relaxed text-[#4d5156]" style="font-family: {{ $previewLocale === 'ar' ? 'Tahoma, Arial, sans-serif' : 'Arial, sans-serif' }}">{{ ($data['home_description'][$previewLocale] ?? '') ?: ($data['home_description']['ar'] ?? '') }}</p>
                </div>
            @endforeach
            <p class="text-[12.5px] leading-relaxed text-[var(--a-muted)]">{{ __('admin.seo.search.note') }}</p>
        </div>
    </div>
</x-admin.panel>
