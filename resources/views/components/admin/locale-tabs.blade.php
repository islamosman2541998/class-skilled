{{--
    Arabic / English tabs for translatable fields with a completeness dot
    (green = complete, amber = English missing). Arabic is required.
    <x-admin.locale-tabs :missing="$this->missingEnglish(['title', 'excerpt'])">
        <x-slot:ar> …fields wire:model="form.title.ar"… </x-slot:ar>
        <x-slot:en> …fields wire:model="form.title.en"… </x-slot:en>
    </x-admin.locale-tabs>
--}}
@props(['missing' => false, 'label' => null])
@php
    // A validation error only in the English fields opens that tab (and marks it), so the error is never hidden.
    $enError = str_contains((string) $en, 'a-error');
    $arError = str_contains((string) $ar, 'a-error');
@endphp
<div x-data="{ tab: 'ar' }" {{ $attributes->merge(['class' => 'space-y-4']) }}>
    <div class="flex flex-wrap items-center justify-between gap-3">
        @if ($label)<p class="a-label mb-0">{{ $label }}</p>@else<span></span>@endif
        <div class="a-segmented" role="tablist">
            <button type="button" role="tab" :aria-selected="tab === 'ar'" @click="tab = 'ar'" class="inline-flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-emerald-500" aria-hidden="true"></span>{{ __('admin.common.arabic') }}
            </button>
            <button type="button" role="tab" :aria-selected="tab === 'en'" @click="tab = 'en'" @class(['inline-flex items-center gap-2', 'text-[var(--a-danger)]' => $enError])>
                <span @class(['h-2 w-2 rounded-full', 'bg-[var(--a-danger)]' => $enError, 'bg-amber-500' => $missing && ! $enError, 'bg-emerald-500' => ! $missing && ! $enError]) aria-hidden="true"></span>English
                @if ($missing)<span class="sr-only">{{ __('admin.common.translation_missing') }}</span>@endif
            </button>
        </div>
    </div>
    <div x-show="tab === 'ar'" dir="rtl" lang="ar" role="tabpanel" class="space-y-4">{{ $ar }}</div>
    @if ($enError && ! $arError)<span hidden x-init="tab = 'en'"></span>@endif
    <div x-show="tab === 'en'" x-cloak dir="ltr" lang="en" role="tabpanel" class="space-y-4">{{ $en }}</div>
</div>
