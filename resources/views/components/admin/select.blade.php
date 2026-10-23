{{--
    Select bound to a Livewire property. Native by default; `searchable` uses Tom Select
    (searchable, RTL), `multiple` gives chips with remove buttons (order = selection order).
    <x-admin.select model="form.role" :options="['admin' => 'مدير']" :label="…" placeholder="…" />
    <x-admin.select model="form.service_ids" :options="$services" multiple searchable :label="…" />
    Add `live` to update on change (wire:model.live).
--}}
@props([
    'model', 'options' => [], 'label' => null, 'required' => false, 'hint' => null, 'placeholder' => null,
    'searchable' => false, 'multiple' => false, 'live' => false, 'id' => null,
])
@php
    $id ??= 'f-'.str_replace(['.', '_'], '-', $model);
    $hasError = $errors->has($model) || $errors->has($model.'.*');
    $wire = $live ? 'wire:model.live' : 'wire:model';
@endphp
<x-admin.field :label="$label" :for="$id" :error="$model" :required="$required" :hint="$hint" {{ $attributes->only('class') }}>
    @if ($searchable || $multiple)
        <div
            wire:ignore
            x-data="tomSelect({ multiple: @js($multiple), placeholder: @js($placeholder ?? __('admin.common.choose')), noResults: @js(__('admin.common.no_options')) })"
            x-modelable="value"
            {{ $wire }}="{{ $model }}"
        >
            <select id="{{ $id }}" x-ref="select" @if ($multiple) multiple @endif @if ($hasError) aria-invalid="true" @endif>
                @unless ($multiple)<option value="">{{ $placeholder ?? __('admin.common.choose') }}</option>@endunless
                @foreach ($options as $value => $text)
                    <option value="{{ $value }}">{{ $text }}</option>
                @endforeach
            </select>
        </div>
    @else
        <select id="{{ $id }}" {{ $wire }}="{{ $model }}" class="a-input" @if ($hasError) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif {{ $attributes->except('class') }}>
            @if ($placeholder !== false)
                <option value="">{{ $placeholder ?? __('admin.common.choose') }}</option>
            @endif
            @foreach ($options as $value => $text)
                <option value="{{ $value }}">{{ $text }}</option>
            @endforeach
        </select>
    @endif
</x-admin.field>
