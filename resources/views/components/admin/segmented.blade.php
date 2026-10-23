{{--
    Segmented control (single choice among a few options).
    <x-admin.segmented model="period" :options="['7' => '7 أيام', '30' => '30 يومًا']" live />
--}}
@props(['model', 'options' => [], 'label' => null, 'live' => false])
<x-admin.field :label="$label" :error="$model" {{ $attributes->only('class') }}>
    <div class="a-segmented" role="radiogroup" x-data="{ value: null }" x-modelable="value" @if ($live) wire:model.live="{{ $model }}" @else wire:model="{{ $model }}" @endif>
        @foreach ($options as $value => $text)
            <button type="button" role="radio" :aria-checked="String(value) === @js((string) $value)" :aria-pressed="String(value) === @js((string) $value)" @click="value = @js($value)">{{ $text }}</button>
        @endforeach
    </div>
</x-admin.field>
