<?php

namespace App\Livewire\Admin\System;

use App\Livewire\Admin\Concerns\WithToasts;
use App\Services\BackupManager;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Throwable;

#[Layout('layouts.admin')]
class Backups extends Component
{
    use WithToasts;

    public bool $withMedia = false;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('manage backups'), 403);
    }

    public function create(BackupManager $backups): void
    {
        abort_unless(auth()->user()?->can('manage backups'), 403);

        try {
            $name = $backups->create($this->withMedia);
            activity()->causedBy(auth()->user())->event('created')->withProperties(['file' => $name])->log('Backup');
            $this->toast('success', __('admin.backups.created'));
        } catch (Throwable $e) {
            report($e);
            $this->toast('error', __('admin.backups.failed'));
        }
    }

    public function delete(string $name, BackupManager $backups): void
    {
        abort_unless(auth()->user()?->can('manage backups'), 403);
        $backups->delete($name);
        $this->toast('success', __('admin.common.deleted'));
    }

    public function render(BackupManager $backups): View
    {
        return view('livewire.admin.system.backups', [
            'backups' => array_map(fn (array $b) => $b + [
                'url' => URL::temporarySignedRoute('admin.backups.download', now()->addMinutes(10), ['name' => $b['name']]),
            ], $backups->list()),
        ])->title(__('admin.nav.backups'));
    }
}
