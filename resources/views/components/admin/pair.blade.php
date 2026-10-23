{{--
    Compact Arabic / English pair of inputs side by side (blocks, repeaters, sections).
    <x-admin.pair model="blocks.0.data.title" :label="__('…')" required />
    <x-admin.pair model="form.items.2.text" textarea rows="2" :label="…" />
    The English field shows an amber dot while empty and Arabic is filled.
--}}
@props(['model', 'label' => null, 'required' => false, 'textarea' => false, 'rows' => 2, 'hint' => null, 'live' => false])
@php
    $id = 'f-'.str_replace(['.', '_'], '-', $model);
    $wire = $live ? 'wire:model.live.debounce.400ms' : 'wire:model';
@endphp
<div {{ $attributes->merge(['class' => 'min-w-0']) }}>
    @if ($label)
        <p class="a-label">{{ $label }}@if ($required)<span class="a-required" aria-hidden="true">*</span>@endif</p>
    @endif
    <div class="grid gap-2 sm:grid-cols-2">
        @foreach (['ar' => 'العربية', 'en' => 'English'] as $loc => $name)
            <div class="relative">
                <label for="{{ $id }}-{{ $loc }}" class="sr-only">{{ $label }} — {{ $name }}</label>
                @if ($textarea)
                    <textarea id="{{ $id }}-{{ $loc }}" {{ $wire }}="{{ $model }}.{{ $loc }}" rows="{{ $rows }}" dir="{{ $loc === 'ar' ? 'rtl' : 'ltr' }}" lang="{{ $loc }}"
                              class="a-input" placeholder="{{ $name }}" @if ($errors->has($model.'.'.$loc)) aria-invalid="true" @endif></textarea>
                @else
                    <input id="{{ $id }}-{{ $loc }}" type="text" {{ $wire }}="{{ $model }}.{{ $loc }}" dir="{{ $loc === 'ar' ? 'rtl' : 'ltr' }}" lang="{{ $loc }}"
                           class="a-input" placeholder="{{ $name }}" @if ($errors->has($model.'.'.$loc)) aria-invalid="true" @endif>
                @endif
                @error($model.'.'.$loc)<p class="a-error" role="alert">{{ $message }}</p>@enderror
            </div>
        @endforeach
    </div>
    @if ($hint)<p class="a-hint">{{ $hint }}</p>@endif
</div>
