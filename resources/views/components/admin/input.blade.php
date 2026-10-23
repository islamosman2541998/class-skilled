{{--
    Text-like input bound to a Livewire property.
    <x-admin.input model="form.name" :label="…" required />
    <x-admin.input model="form.phone" type="tel" :label="…" />        (tel/email/url are LTR)
    <x-admin.input model="search" live :label="…" />                  (wire:model.live.debounce.300ms)
    Props: type, required, hint, placeholder, dir, live, disabled, readonly, prefix (text shown before the input).
--}}
@props([
    'model', 'label' => null, 'type' => 'text', 'required' => false, 'hint' => null, 'placeholder' => null,
    'dir' => null, 'live' => false, 'disabled' => false, 'readonly' => false, 'prefix' => null, 'id' => null,
])
@php
    $id ??= 'f-'.str_replace(['.', '_'], '-', $model);
    $dir ??= in_array($type, ['email', 'tel', 'url', 'number'], true) ? 'ltr' : null;
    $hasError = $errors->has($model);
@endphp
<x-admin.field :label="$label" :for="$id" :error="$model" :required="$required" :hint="$hint" {{ $attributes->only('class') }}>
    <div @class(['flex items-stretch' => $prefix])>
        @if ($prefix)
            <span class="flex items-center rounded-s-[10px] border border-e-0 border-a-border bg-a-bg px-3 text-[13px] text-[var(--a-muted)]" dir="ltr">{{ $prefix }}</span>
        @endif
        <input
            id="{{ $id }}"
            type="{{ $type }}"
            @if ($live) wire:model.live.debounce.300ms="{{ $model }}" @else wire:model="{{ $model }}" @endif
            @class(['a-input', 'rounded-s-none' => $prefix])
            @if ($dir) dir="{{ $dir }}" @endif
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            @if ($required) aria-required="true" @endif
            @if ($hasError) aria-invalid="true" aria-describedby="{{ $id }}-error" @elseif ($hint) aria-describedby="{{ $id }}-hint" @endif
            @disabled($disabled)
            @readonly($readonly)
            {{ $attributes->except('class') }}
        >
    </div>
</x-admin.field>
