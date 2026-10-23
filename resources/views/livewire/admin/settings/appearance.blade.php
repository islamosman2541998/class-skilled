<div x-data="dirtyGuard(@js(__('admin.common.unsaved_changes')))">
    <x-admin.page-header :title="__('admin.nav.appearance')" :subtitle="__('admin.appearance.subtitle')"
        :breadcrumbs="[__('admin.nav.navigation') => null, __('admin.nav.appearance') => null]">
        <x-slot:actions>
            <button type="submit" form="settings-form" class="a-btn a-btn-primary">{{ __('admin.settings.save') }}</button>
        </x-slot:actions>
    </x-admin.page-header>

    @php($sample = ['ar' => ['title' => 'نبني مشاريع تتكلم عنا', 'text' => 'نهتم بالتفاصيل الصغيرة بقدر اهتمامنا بالصورة الكبيرة.'], 'en' => ['title' => 'Projects that speak for us', 'text' => 'We care about small details as much as the big picture.']])
    @php($fonts = ['cairo' => 'Cairo', 'tajawal' => 'Tajawal'])

    <form id="settings-form" wire:submit="save" class="grid gap-6 xl:grid-cols-2">
        <div class="space-y-6">
            <x-admin.panel :title="__('admin.appearance.fonts')" :subtitle="__('admin.appearance.fonts_hint')">
                <div class="space-y-6">
                    <x-admin.radio-cards model="data.heading_font" live :label="__('admin.appearance.heading_font')"
                        :options="collect($fonts)->map(fn ($name, $key) => ['title' => $sample['ar']['title'], 'text' => $name.' — '.$sample['en']['title'], 'style' => 'font-family:'.$name.';font-weight:800'])->all()" />
                    <x-admin.radio-cards model="data.body_font" live :label="__('admin.appearance.body_font')"
                        :options="collect($fonts)->map(fn ($name, $key) => ['title' => $sample['ar']['text'], 'text' => $name.' — '.$sample['en']['text'], 'style' => 'font-family:'.$name.';font-weight:400'])->all()" />
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-admin.select model="data.base_font_size" live :label="__('admin.appearance.base_size')" :options="['15' => '15px', '15.5' => '15.5px', '16' => '16px']" :placeholder="false" />
                        <x-admin.select model="data.line_height_ar" live :label="__('admin.appearance.line_height')" :options="['1.7' => '1.7', '1.8' => '1.8', '1.85' => '1.85', '1.9' => '1.9']" :placeholder="false" />
                    </div>
                </div>
            </x-admin.panel>

            <x-admin.panel :title="__('admin.appearance.colors')" :subtitle="__('admin.appearance.colors_hint')">
                <x-slot:actions>
                    <button type="button" class="a-link text-[13.5px]" wire:click="restoreColors">{{ __('admin.appearance.restore') }}</button>
                </x-slot:actions>
                @foreach ($this->warnings() as $warning)
                    <p class="mb-3 rounded-lg bg-amber-50 px-3 py-2 text-[13px] font-semibold text-amber-900" role="alert">{{ $warning }}</p>
                @endforeach
                <div class="divide-y divide-a-border">
                    @foreach (\App\Livewire\Admin\Settings\Appearance::COLOR_KEYS as $key)
                        <div class="flex flex-wrap items-center justify-between gap-4 py-3" wire:key="color-{{ $key }}">
                            <div class="flex items-center gap-3">
                                <span class="h-10 w-10 rounded-lg border border-a-border" style="background: {{ $data['colors'][$key] ?? '#fff' }}"></span>
                                <div>
                                    <p class="font-bold">{{ __('admin.appearance.color_names.'.$key) }}</p>
                                    <p class="text-[12.5px] text-[var(--a-muted)]">{{ __('admin.appearance.color_uses.'.$key) }}</p>
                                </div>
                            </div>
                            <x-admin.color model="data.colors.{{ $key }}" class="w-56" />
                        </div>
                    @endforeach
                </div>
            </x-admin.panel>
        </div>

        <div class="space-y-6">
            <x-admin.panel :title="__('admin.appearance.preview')" :padded="false" x-data="{ lang: 'ar' }">
                <x-slot:actions>
                    <div class="a-segmented">
                        <button type="button" :aria-pressed="lang === 'ar'" @click="lang = 'ar'">العربية</button>
                        <button type="button" :aria-pressed="lang === 'en'" @click="lang = 'en'">English</button>
                    </div>
                </x-slot:actions>
                <style>{!! $this->previewCss() !!}</style>
                <div id="cs-preview" style="font-family: var(--font-body); font-size: var(--base-size); color: var(--c-ink)">
                    @foreach (['ar', 'en'] as $lang)
                        <div x-show="lang === '{{ $lang }}'" dir="{{ $lang === 'ar' ? 'rtl' : 'ltr' }}" lang="{{ $lang }}" @if ($lang === 'en') x-cloak @endif>
                            <div class="px-8 py-10" style="background: var(--c-deep); color: #fff">
                                <p style="font-family: var(--font-head); font-weight: 800; font-size: 34px; line-height: 1.3">{{ $sample[$lang]['title'] }}</p>
                                <p class="mt-3" style="color: #c9d3e3; line-height: {{ $lang === 'ar' ? 'var(--lh-ar)' : '1.65' }}">{{ $sample[$lang]['text'] }}</p>
                                <span class="mt-6 inline-flex h-11 items-center rounded-full px-6 font-bold" style="background: var(--c-sky); color: var(--c-deep)">{{ $lang === 'ar' ? 'اطلب عرض سعر' : 'Request a quote' }}</span>
                            </div>
                            <div class="px-8 py-8" style="background: var(--c-paper)">
                                <div class="rounded-2xl p-5" style="background: #fff; border: 1px solid var(--c-line)">
                                    <p style="font-family: var(--font-head); font-weight: 700; font-size: 17px; color: var(--c-navy)">{{ $lang === 'ar' ? 'أرضيات الإيبوكسي' : 'Epoxy flooring' }}</p>
                                    <p class="mt-1" style="color: var(--c-muted); line-height: {{ $lang === 'ar' ? 'var(--lh-ar)' : '1.65' }}">{{ $lang === 'ar' ? 'للمستودعات والمعارض والمواقف والمطابخ التجارية، بسماكات وأنظمة متعددة.' : 'For warehouses, showrooms, car parks and commercial kitchens, in several systems.' }}</p>
                                    <span class="mt-4 inline-flex h-10 items-center rounded-full border-2 px-5 text-[14px] font-bold" style="border-color: var(--c-navy); color: var(--c-navy)">{{ $lang === 'ar' ? 'التفاصيل' : 'Details' }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-admin.panel>

            <x-admin.panel :title="__('admin.appearance.logos')">
                <x-slot:actions>
                    @if (Route::has('admin.settings.general'))
                        <a href="{{ route('admin.settings.general') }}" wire:navigate class="a-link text-[13.5px]">{{ __('admin.common.edit') }}</a>
                    @endif
                </x-slot:actions>
                <div class="grid grid-cols-3 gap-4">
                    @foreach (['logo' => 'bg-white', 'logo_white' => 'bg-[var(--c-deep)]', 'favicon' => 'bg-white'] as $key => $bg)
                        <figure class="text-center">
                            <div class="flex h-24 items-center justify-center rounded-xl border border-a-border p-3 {{ $bg }}">
                                @if ($logos[$key])
                                    <img src="{{ $logos[$key]->url('sm') }}" alt="" class="max-h-full max-w-full object-contain">
                                @else
                                    <img src="{{ asset('images/brand/'.($key === 'logo_white' ? 'logo-white' : ($key === 'favicon' ? 'mark' : 'logo')).'.png') }}" alt="" class="max-h-full max-w-full object-contain">
                                @endif
                            </div>
                            <figcaption class="mt-2 text-[12.5px] text-[var(--a-muted)]">{{ __('admin.settings.fields.'.$key) }}</figcaption>
                        </figure>
                    @endforeach
                </div>
            </x-admin.panel>
        </div>
    </form>
</div>
