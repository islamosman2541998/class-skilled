{{-- Public form field bound to a Livewire property: $model, $label, $type (text|email|tel|textarea), $required, $hint, $ltr, $live. --}}
@php
    $id = ($prefix ?? 'f').'-'.$model;
    $type ??= 'text';
    $invalid = $errors->has($model);
    $classes = 'w-full rounded-[12px] border bg-white px-4 text-[15px] focus:border-sky focus:outline-none '.($invalid ? 'border-[#b42318]' : 'border-line');
@endphp
<div @class([$class ?? ''])>
    <label for="{{ $id }}" class="mb-1.5 block text-[14px] font-bold text-ink">
        {{ $label }}
        @if ($required ?? false)
            <span class="text-[#b42318]" aria-hidden="true">*</span>
        @endif
    </label>
    @if ($type === 'textarea')
        <textarea id="{{ $id }}" wire:model.blur="{{ $model }}" rows="{{ $rows ?? 5 }}" maxlength="{{ $max ?? 3000 }}" class="{{ $classes }} py-3" @if ($invalid) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif @if ($required ?? false) aria-required="true" @endif></textarea>
    @else
        <input id="{{ $id }}" type="{{ $type }}" wire:model.blur="{{ $model }}" maxlength="{{ $max ?? 150 }}" class="{{ $classes }} h-12 @if ($ltr ?? false) text-start @endif"
            @if ($ltr ?? false) dir="ltr" @endif
            @if ($type === 'tel') inputmode="tel" autocomplete="tel" placeholder="{{ __('forms.phone_hint') }}" @endif
            @if ($type === 'email') inputmode="email" autocomplete="email" @endif
            @if ($model === 'name') autocomplete="name" @endif
            @if ($invalid) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
            @if ($required ?? false) aria-required="true" @endif>
    @endif
    @error($model)
        <p id="{{ $id }}-error" class="mt-1.5 text-[13.5px] text-[#b42318]">{{ $message }}</p>
    @enderror
</div>
