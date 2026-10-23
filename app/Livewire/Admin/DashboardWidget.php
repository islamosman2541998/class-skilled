<?php

namespace App\Livewire\Admin;

use App\Services\DashboardStats;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Reactive;
use Livewire\Component;

/**
 * One dashboard widget, loaded after the page with a skeleton placeholder.
 * Widgets: stats, chart, services, latest, attention, pages.
 */
#[Lazy]
class DashboardWidget extends Component
{
    public const WIDGETS = ['stats', 'chart', 'services', 'latest', 'attention', 'pages'];

    /** Widgets that show visitor requests (need "view quote_requests"). */
    public const REQUEST_WIDGETS = ['stats', 'chart', 'services', 'latest'];

    #[Locked]
    public string $name = 'stats';

    #[Reactive]
    public int $period = 30;

    public string $series = 'requests';

    public function mount(string $name): void
    {
        abort_unless(in_array($name, self::WIDGETS, true), 404);
        abort_unless(auth()->user()?->can('view dashboard'), 403);
        $this->name = $name;
    }

    public function placeholder(): View
    {
        return view('livewire.admin.dashboard.placeholder', ['name' => $this->name]);
    }

    public function render(DashboardStats $stats): View
    {
        $days = DashboardStats::period($this->period);
        $canRequests = (bool) auth()->user()?->can('view quote_requests');

        $data = match ($this->name) {
            'stats' => ['stats' => $stats->stats($days), 'canRequests' => $canRequests],
            'chart' => ['daily' => $stats->daily($days)],
            'services' => ['rows' => $stats->byService($days)],
            'latest' => ['quotes' => $canRequests ? $stats->latestQuotes() : collect()],
            'attention' => ['items' => $stats->attention(), 'licences' => $stats->licences()],
            default => ['rows' => $stats->topPages($days)],
        };

        return view('livewire.admin.dashboard.'.$this->name, $data + ['days' => $days, 'canRequests' => $canRequests]);
    }
}
