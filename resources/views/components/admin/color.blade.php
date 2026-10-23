{{--
    Colour input: hex text field + swatch picker.
    <x-admin.color model="form.colors.navy" :label="…" />
--}}
@props(['model', 'label' => null, 'hint' => null, 'id' => null, 'live' => true])
@php($id ??= 'f-'.str_replace(['.', '_'], '-', $model))
<x-admin.field :label="$label" :for="$id" :error="$model" :hint="$hint" {{ $attributes->only('class') }}>
    <div class="flex items-center gap-2" x-data="{ value: '' }" x-modelable="value" @if ($live) wire:model.live.debounce.250ms="{{ $model }}" @else wire:model="{{ $model }}" @endif>
        <input type="color" x-model="value" class="h-[42px] w-12 shrink-0 cursor-pointer rounded-[10px] border border-a-border bg-white p-1" aria-label="{{ $label }}">
        <input id="{{ $id }}" type="text" x-model="value" maxlength="7" dir="ltr" class="a-input font-mono uppercase" placeholder="#2B4168" @if ($errors->has($model)) aria-invalid="true" @endif>
    </div>
</x-admin.field>
