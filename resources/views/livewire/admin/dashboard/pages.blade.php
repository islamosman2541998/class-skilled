@php($max = max(1, (int) collect($rows)->max('views')))
<x-admin.panel :title="__('admin.dashboard.top_pages')" :subtitle="__('admin.dashboard.days', ['count' => $days])">
    @if ($rows === [])
        <p class="py-6 text-center text-[13.5px] text-[var(--a-muted)]">{{ __('admin.dashboard.no_data') }}</p>
    @else
        <ul class="grid gap-x-8 gap-y-3 md:grid-cols-2">
            @foreach ($rows as $row)
                <li>
                    <div class="flex justify-between gap-3 text-[13.5px]">
                        <a href="{{ url($row['path']) }}" target="_blank" rel="noopener" class="truncate hover:underline" dir="auto">{{ $row['path'] }}</a>
                        <bdi class="font-bold">{{ $row['views'] }}</bdi>
                    </div>
                    <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-[#eef1f6]"><div class="h-full rounded-full bg-[var(--c-sky)]" style="width: {{ round($row['views'] / $max * 100) }}%"></div></div>
                </li>
            @endforeach
        </ul>
    @endif
</x-admin.panel>
