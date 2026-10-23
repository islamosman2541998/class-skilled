{{--
    Stat card for the dashboard home.
    <x-admin.stat-card :label="…" :value="48" delta="+12" trend="up" :note="…" />
--}}
@props(['label', 'value', 'delta' => null, 'trend' => null, 'note' => null])
<div {{ $attributes->merge(['class' => 'a-panel p-5']) }}>
    <p class="text-[13.5px] text-[var(--a-muted)]">{{ $label }}</p>
    <p class="mt-2 font-[family-name:var(--font-head)] text-[30px] font-extrabold leading-none text-[var(--c-deep)]"><bdi dir="ltr">{{ $value }}</bdi></p>
    @if ($delta !== null || $note)
        <p @class([
            'mt-3 text-[13px] font-semibold',
            'text-[var(--a-success)]' => $trend === 'up',
            'text-[var(--a-danger)]' => $trend === 'down',
            'text-[var(--a-muted)]' => ! in_array($trend, ['up', 'down'], true),
        ])>
            @if ($delta !== null)<bdi dir="ltr">{{ $delta }}</bdi>@endif {{ $note }}
        </p>
    @endif
    {{ $slot }}
</div>
