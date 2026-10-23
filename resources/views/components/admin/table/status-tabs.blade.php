{{--
    Quick status tabs with counts, bound to `filters.{key}` ("" = all).
    <x-admin.table.status-tabs key="status" :value="$filters['status'] ?? ''" :tabs="['' => [__('admin.common.all'), 48], 'new' => ['جديد', 6]]" />
--}}
@props(['key' => 'status', 'tabs' => [], 'value' => ''])
<div {{ $attributes->merge(['class' => 'mb-4 flex flex-wrap gap-2']) }} role="tablist">
    @foreach ($tabs as $tabValue => [$label, $count])
        <button type="button" role="tab" class="a-chip" aria-pressed="{{ (string) $value === (string) $tabValue ? 'true' : 'false' }}"
                aria-selected="{{ (string) $value === (string) $tabValue ? 'true' : 'false' }}"
                wire:click="setFilter('{{ $key }}', @js((string) $tabValue))">
            <span>{{ $label }}</span>
            @if ($count !== null)<span class="count" dir="ltr">{{ $count }}</span>@endif
        </button>
    @endforeach
</div>
