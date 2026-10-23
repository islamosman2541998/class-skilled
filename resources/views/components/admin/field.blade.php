{{--
    Field wrapper: label, required marker, hint and inline validation error.
    Used by every form control; can wrap custom markup too:
    <x-admin.field :label="…" for="f-x" error="form.x" :required="true" :hint="…"> <input id="f-x" …> </x-admin.field>
--}}
@props(['label' => null, 'for' => null, 'error' => null, 'required' => false, 'hint' => null])
<div {{ $attributes->merge(['class' => 'min-w-0']) }}>
    @if ($label)
        <label @if ($for) for="{{ $for }}" @endif class="a-label">
            {{ $label }}@if ($required)<span class="a-required" aria-hidden="true">*</span><span class="sr-only">({{ __('admin.common.required') }})</span>@endif
        </label>
    @endif
    {{ $slot }}
    @if ($hint)
        <p class="a-hint" @if ($for) id="{{ $for }}-hint" @endif>{{ $hint }}</p>
    @endif
    @if ($error)
        @error($error)
            <p class="a-error" @if ($for) id="{{ $for }}-error" @endif role="alert">{{ $message }}</p>
        @enderror
    @endif
</div>
