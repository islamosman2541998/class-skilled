<?php

namespace App\Livewire\Admin;

use App\Services\DashboardStats;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Dashboard home: period switch (7 / 30 / 90 days) and lazy-loaded widgets
 * (App\Livewire\Admin\DashboardWidget) fed by App\Services\DashboardStats.
 */
#[Layout('layouts.admin')]
class Dashboard extends Component
{
    #[Url(except: 30)]
    public int $period = 30;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('view dashboard'), 403);
        $this->period = DashboardStats::period($this->period);
    }

    public function setPeriod(int $days): void
    {
        $this->period = DashboardStats::period($days);
    }

    public function render(): View
    {
        return view('livewire.admin.dashboard')->title(__('admin.dashboard.title'));
    }
}
