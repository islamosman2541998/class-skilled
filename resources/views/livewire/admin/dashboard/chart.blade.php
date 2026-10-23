@php
    $config = [
        'type' => 'bar',
        'data' => [
            'labels' => $daily['labels'],
            'datasets' => [[
                'label' => __('admin.dashboard.series_'.$series),
                'data' => $daily[$series === 'visits' ? 'visits' : 'requests'],
                'backgroundColor' => array_map(fn ($current) => $current ? '#4FC3EE' : '#2B4168', $daily['current']),
                'borderRadius' => 6,
                'maxBarThickness' => 26,
            ]],
        ],
        'options' => [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => ['legend' => ['display' => false]],
            'scales' => [
                'y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]],
                'x' => ['reverse' => app()->getLocale() === 'ar', 'grid' => ['display' => false]],
            ],
        ],
    ];
@endphp
<x-admin.panel :title="__('admin.dashboard.chart_title')" :subtitle="__('admin.dashboard.chart_hint')">
    <x-slot:actions>
        <div class="flex gap-1" role="group">
            @foreach (['requests', 'visits'] as $option)
                <button type="button" wire:click="$set('series', '{{ $option }}')" class="a-chip" aria-pressed="{{ $series === $option ? 'true' : 'false' }}">{{ __('admin.dashboard.series_'.$option) }}</button>
            @endforeach
        </div>
    </x-slot:actions>
    <div class="h-64" wire:key="chart-{{ $series }}-{{ $days }}">
        <canvas x-data="chart(@js($config))" role="img" aria-label="{{ __('admin.dashboard.chart_title') }}"></canvas>
    </div>
</x-admin.panel>
