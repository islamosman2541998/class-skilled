{{--
    Textarea with optional character counter.
    <x-admin.textarea model="form.excerpt.ar" :label="…" :max="160" rows="3" />
--}}
@props(['model', 'label' => null, 'required' => false, 'hint' => null, 'max' => null, 'rows' => 4, 'placeholder' => null, 'id' => null, 'dir' => null, 'live' => false])
@php
    $id ??= 'f-'.str_replace(['.', '_'], '-', $model);
    $hasError = $errors->has($model);
@endphp
<x-admin.field :label="$label" :for="$id" :error="$model" :required="$required" :hint="$hint" {{ $attributes->only('class') }}>
    <div x-data="{ count: 0 }" x-init="count = $refs.area.value.length">
        <textarea
            id="{{ $id }}"
            x-ref="area"
            rows="{{ $rows }}"
            @if ($live) wire:model.live.debounce.400ms="{{ $model }}" @else wire:model="{{ $model }}" @endif
            x-on:input="count = $event.target.value.length"
            class="a-input"
            @if ($dir) dir="{{ $dir }}" @endif
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            @if ($max) maxlength="{{ $max * 2 }}" @endif
            @if ($required) aria-required="true" @endif
            @if ($hasError) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
            {{ $attributes->except('class') }}
        ></textarea>
        @if ($max)
            <p class="mt-1 text-end text-[12px]" :class="count > {{ $max }} ? 'text-[var(--a-danger)] font-bold' : 'text-[var(--a-muted)]'">
                <span x-text="count"></span> / {{ $max }}
            </p>
        @endif
    </div>
</x-admin.field>
