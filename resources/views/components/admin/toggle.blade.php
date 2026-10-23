{{--
    On/off switch bound to a boolean property.
    <x-admin.toggle model="form.is_active" :label="…" :hint="…" />
    `live` sends the change immediately (wire:model.live).
--}}
@props(['model', 'label' => null, 'hint' => null, 'live' => false, 'disabled' => false, 'id' => null])
@php($id ??= 'f-'.str_replace(['.', '_'], '-', $model))
<div {{ $attributes->merge(['class' => 'flex items-start justify-between gap-4']) }}
     x-data="{ on: false }" x-modelable="on" @if ($live) wire:model.live="{{ $model }}" @else wire:model="{{ $model }}" @endif>
    @if ($label)
        <div>
            <label for="{{ $id }}" class="text-[14px] font-semibold text-[var(--c-deep)]">{{ $label }}</label>
            @if ($hint)<p class="a-hint mt-0.5">{{ $hint }}</p>@endif
            @error($model)<p class="a-error">{{ $message }}</p>@enderror
        </div>
    @endif
    <button id="{{ $id }}" type="button" role="switch" class="a-toggle mt-0.5" :aria-checked="on ? 'true' : 'false'" @click="on = ! on" @disabled($disabled)>
        @unless ($label)<span class="sr-only">{{ $model }}</span>@endunless
    </button>
</div>
