<?php

namespace App\Livewire\Admin;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Dashboard home. Phase 02: header + empty stat cards (widgets arrive in phase 09).
 */
#[Layout('layouts.admin')]
class Dashboard extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->user()?->can('view dashboard'), 403);
    }

    public function render(): View
    {
        return view('livewire.admin.dashboard')->title(__('admin.dashboard.title'));
    }
}
