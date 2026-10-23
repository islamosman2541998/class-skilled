@php($canRequests = auth()->user()->can('view quote_requests'))
<div>
    <x-admin.page-header
        :title="now()->hour < 12 ? __('admin.dashboard.greeting_morning') : __('admin.dashboard.greeting_evening')"
        :subtitle="__('admin.dashboard.subtitle', ['days' => $period])"
    >
        <x-slot:actions>
            <div class="flex gap-1 rounded-full border border-a-border bg-white p-1" role="group" aria-label="{{ __('admin.dashboard.period') }}">
                @foreach (\App\Services\DashboardStats::PERIODS as $days)
                    <button type="button" wire:click="setPeriod({{ $days }})" @class(['rounded-full px-4 py-1.5 text-[13px] font-bold', 'bg-[var(--c-navy)] text-white' => $period === $days, 'text-[var(--a-muted)] hover:text-[var(--c-deep)]' => $period !== $days]) aria-pressed="{{ $period === $days ? 'true' : 'false' }}">
                        {{ __('admin.dashboard.days', ['count' => $days]) }}
                    </button>
                @endforeach
            </div>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="space-y-6">
        <livewire:admin.dashboard-widget name="stats" :period="$period" wire:key="w-stats" />

        @if ($canRequests)
            <div class="grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
                <livewire:admin.dashboard-widget name="chart" :period="$period" wire:key="w-chart" />
                <livewire:admin.dashboard-widget name="services" :period="$period" wire:key="w-services" />
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
            @if ($canRequests)
                <livewire:admin.dashboard-widget name="latest" :period="$period" wire:key="w-latest" />
            @endif
            <livewire:admin.dashboard-widget name="attention" :period="$period" wire:key="w-attention" />
        </div>

        <livewire:admin.dashboard-widget name="pages" :period="$period" wire:key="w-pages" />
    </div>
</div>
