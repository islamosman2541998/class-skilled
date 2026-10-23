{{--
    Repeater: add, remove, reorder (drag) and collapse a list of items stored in an array property.
    The component using it must `use WithRepeaters` (Livewire concern).

    <x-admin.repeater model="form.items" :label="…" :template="['title' => ['ar' => '', 'en' => '']]" :add-label="…">
        @foreach ($form['items'] as $i => $item)
            <x-admin.repeater-item model="form.items" :index="$i" :item="$item" :title="$item['title']['ar'] ?? ''">
                <x-admin.input model="form.items.{{ $i }}.title.ar" … />
            </x-admin.repeater-item>
        @endforeach
    </x-admin.repeater>
--}}
@props(['model', 'label' => null, 'template' => [], 'addLabel' => null, 'hint' => null, 'max' => null, 'count' => null])
<x-admin.field :label="$label" :error="$model" :hint="$hint" {{ $attributes->only('class') }}>
    <div class="space-y-2" x-sortable.handle="sortRepeater('{{ $model }}', __ids)">
        {{ $slot }}
    </div>
    @if ($max === null || $count === null || $count < $max)
        <button type="button" class="a-btn a-btn-secondary a-btn-sm mt-3" wire:click="addRepeaterItem('{{ $model }}', @js($template))">
            <x-heroicon-m-plus class="h-4 w-4" /> {{ $addLabel ?? __('admin.common.add_item') }}
        </button>
    @endif
</x-admin.field>
