<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach (['quotes' => __('admin.dashboard.stat_quotes'), 'won' => __('admin.dashboard.stat_won'), 'clicks' => __('admin.dashboard.stat_clicks'), 'visits' => __('admin.dashboard.stat_visits')] as $key => $label)
        @continue(! $canRequests && in_array($key, ['quotes', 'won'], true))
        @php([$delta, $trend] = \App\Services\DashboardStats::delta((float) $stats[$key]['current'], (float) $stats[$key]['previous']))
        <x-admin.stat-card :label="$label" :value="$stats[$key]['value']" :delta="$delta" :trend="$trend" :note="__('admin.dashboard.vs_previous')" />
    @endforeach
</div>
