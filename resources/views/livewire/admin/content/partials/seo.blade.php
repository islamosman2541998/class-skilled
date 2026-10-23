{{-- SEO panel with Google snippet preview per locale. Vars: $fallbackTitle (pair), $fallbackDescription (pair), $urls (locale => url|null) --}}
<x-admin.panel title="SEO">
    <x-admin.locale-tabs :missing="$this->missingEnglish([$seo['title'], $seo['description']])">
        @foreach (['ar', 'en'] as $loc)
            <x-slot :name="$loc">
                <div x-data="{ t: @js($seo['title'][$loc] ?? ''), d: @js($seo['description'][$loc] ?? '') }">
                    <x-admin.input model="seo.title.{{ $loc }}" :label="__('admin.content.seo_title')" x-on:input="t = $event.target.value" />
                    <p class="mt-1 text-[12px]" :class="t.length > 60 ? 'text-[var(--a-danger)] font-bold' : 'text-[var(--a-success)]'"><span x-text="t.length"></span> / 60</p>
                    <x-admin.textarea model="seo.description.{{ $loc }}" :label="__('admin.content.seo_description')" rows="3" x-on:input="d = $event.target.value" class="mt-4" />
                    <p class="mt-1 text-[12px]" :class="d.length > 160 ? 'text-[var(--a-danger)] font-bold' : 'text-[var(--a-success)]'"><span x-text="d.length"></span> / 160</p>
                    @php($snippet = $this->snippet($loc, \App\Support\Translate::pick($fallbackTitle, $loc), \App\Support\Translate::pick($fallbackDescription, $loc), $urls[$loc] ?? null))
                    <p class="a-label mt-5">{{ __('admin.content.snippet') }}</p>
                    <div class="rounded-xl border border-a-border bg-white p-4">
                        <p class="truncate text-[12px] text-[#4d5156]" dir="ltr">{{ $snippet['url'] ?: url('/') }}</p>
                        <p class="mt-1 text-[17px] leading-snug text-[#1a0dab]" x-text="(t || @js(\App\Support\Translate::pick($fallbackTitle, $loc))) + @js(($s = \App\Support\Translate::pick(app(\App\Settings\SeoSettings::class)->title_suffix, $loc)) ? ' | '.$s : '')"></p>
                        <p class="mt-1 text-[13px] leading-relaxed text-[#4d5156]" x-text="(d || @js($snippet['description'])).slice(0, 160)"></p>
                    </div>
                    <x-admin.input model="seo.canonical.{{ $loc }}" type="url" :label="__('admin.content.canonical')" :hint="__('admin.content.canonical_hint')" class="mt-4" />
                </div>
            </x-slot>
        @endforeach
    </x-admin.locale-tabs>
    <div class="mt-5 space-y-4 border-t border-a-border pt-5">
        <livewire:admin.asset-field wire:model="seo.og_asset_id" kind="image" :label="__('admin.content.og_image')" :hint="__('admin.content.og_image_hint')" :error="$errors->first('seo.og_asset_id')" wire:key="seo-og" />
        <x-admin.segmented model="seo.robots" :label="__('admin.content.robots')" :options="['index' => __('admin.content.robots_index'), 'noindex' => __('admin.content.robots_noindex')]" />
    </div>
</x-admin.panel>
