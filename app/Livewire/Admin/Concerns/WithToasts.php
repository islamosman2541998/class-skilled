<?php

namespace App\Livewire\Admin\Concerns;

/**
 * Toasts from Livewire components (Notyf, see resources/js/admin/toast.js).
 *
 *   $this->toast('success', __('admin.common.saved'));
 *   $this->flashToast('success', …); $this->redirectRoute('admin.users.index', navigate: true);
 */
trait WithToasts
{
    protected function toast(string $type, string $message): void
    {
        $this->dispatch('toast', type: $type, message: $message);
    }

    /**
     * Toast shown on the next page (after a redirect / wire:navigate).
     */
    protected function flashToast(string $type, string $message): void
    {
        session()->flash('toast', ['type' => $type, 'message' => $message]);
    }
}
