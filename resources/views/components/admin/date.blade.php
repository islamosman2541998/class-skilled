{{--
    Date / datetime picker (flatpickr, Arabic locale). Value format: Y-m-d or Y-m-d H:i.
    <x-admin.date model="form.published_at" :label="…" time />
--}}
@props(['model', 'label' => null, 'required' => false, 'hint' => null, 'time' => false, 'id' => null])
@php($id ??= 'f-'.str_replace(['.', '_'], '-', $model))
<x-admin.field :label="$label" :for="$id" :error="$model" :required="$required" :hint="$hint" {{ $attributes->only('class') }}>
    <div wire:ignore x-data="datePicker({ time: @js($time) })" x-modelable="value" wire:model="{{ $model }}" class="relative">
        <input id="{{ $id }}" x-ref="input" type="text" class="a-input pe-10" dir="ltr" autocomplete="off" @if ($errors->has($model)) aria-invalid="true" @endif>
        <button type="button" class="absolute inset-y-0 end-0 flex w-10 items-center justify-center text-[var(--a-muted)] hover:text-[var(--a-danger)]" @click="clear()" aria-label="{{ __('admin.common.remove') }}" x-show="value">
            <x-heroicon-m-x-mark class="h-4 w-4" />
        </button>
    </div>
</x-admin.field>
