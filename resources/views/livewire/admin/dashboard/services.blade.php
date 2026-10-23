@php($max = max(1, (int) collect($rows)->max('total')))
<x-admin.panel :title="__('admin.dashboard.by_service')">
    @if ($rows === [])
        <p class="py-6 text-center text-[13.5px] text-[var(--a-muted)]">{{ __('admin.dashboard.no_data') }}</p>
    @else
        <ul class="space-y-3">
            @foreach ($rows as $row)
                <li>
                    <div class="flex justify-between gap-3 text-[13.5px]"><span class="truncate">{{ $row['label'] }}</span><bdi class="font-bold">{{ $row['total'] }}</bdi></div>
                    <div class="mt-1 h-2 overflow-hidden rounded-full bg-[#eef1f6]"><div class="h-full rounded-full bg-[var(--c-navy)]" style="width: {{ round($row['total'] / $max * 100) }}%"></div></div>
                </li>
            @endforeach
        </ul>
    @endif
</x-admin.panel>
