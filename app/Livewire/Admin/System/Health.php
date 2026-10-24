<?php

namespace App\Livewire\Admin\System;

use App\Services\SiteCache;
use App\Support\SiteCheck;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Artisan;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * System → Health: the `site:check` report in the dashboard (for hosts without SSH),
 * plus cache maintenance buttons.
 */
#[Layout('layouts.admin')]
class Health extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->user()?->can('manage settings'), 403);
    }

    public function optimize(): void
    {
        abort_unless(auth()->user()?->can('manage settings'), 403);
        Artisan::call('optimize'); // includes route:trans:cache (AppServiceProvider::optimizes)
        $this->dispatch('toast', type: 'success', message: __('admin.health.optimized'));
    }

    public function clearCaches(): void
    {
        abort_unless(auth()->user()?->can('manage settings'), 403);
        Artisan::call('optimize:clear');
        app(SiteCache::class)->flush();
        $this->dispatch('toast', type: 'success', message: __('admin.health.cleared'));
    }

    public function render(SiteCheck $check): View
    {
        $results = $check->run();

        return view('livewire.admin.system.health', [
            'groups' => collect($results)->groupBy('group'),
            'failed' => SiteCheck::failed($results),
        ])->title(__('admin.nav.health'));
    }
}
