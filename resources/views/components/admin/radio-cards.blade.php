{{--
    Large selectable cards (single choice), e.g. font choice.
    <x-admin.radio-cards model="form.heading_font" :options="['cairo' => ['title' => 'Cairo', 'text' => '…', 'style' => 'font-family:Cairo']]" />
    Each option: string label or array {title, text?, style?}. Extra content per option via the `option` slot is not needed.
--}}
@props(['model', 'options' => [], 'label' => null, 'columns' => 2, 'live' => false])
<x-admin.field :label="$label" :error="$model" {{ $attributes->only('class') }}>
    <div role="radiogroup" @class(['grid gap-3', 'sm:grid-cols-2' => $columns == 2, 'sm:grid-cols-3' => $columns == 3, 'sm:grid-cols-4' => $columns == 4]) x-data="{ value: null }" x-modelable="value" @if ($live) wire:model.live="{{ $model }}" @else wire:model="{{ $model }}" @endif>
        @foreach ($options as $value => $option)
            @php($option = is_array($option) ? $option : ['title' => $option])
            <button type="button" role="radio" :aria-checked="String(value) === @js((string) $value)" @click="value = @js($value)"
                    class="rounded-xl border p-4 text-start transition"
                    :class="String(value) === @js((string) $value) ? 'border-[var(--c-navy)] bg-[#f3f6fb] ring-2 ring-[var(--c-sky)]/40' : 'border-a-border bg-white hover:border-[#c8d1de]'">
                <span class="block font-bold text-[var(--c-deep)]" @isset($option['style']) style="{{ $option['style'] }}" @endisset>{{ $option['title'] }}</span>
                @isset($option['text'])<span class="mt-1 block text-[13px] text-[var(--a-muted)]" @isset($option['style']) style="{{ $option['style'] }}" @endisset>{{ $option['text'] }}</span>@endisset
            </button>
        @endforeach
    </div>
</x-admin.field>
