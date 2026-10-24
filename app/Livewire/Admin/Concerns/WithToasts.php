<?php

namespace App\Livewire\Admin\Concerns;

use Illuminate\Validation\ValidationException;

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
     * Livewire hook: a failed validation also raises an error toast naming the first problem, so a save that
     * did not happen is never silent (the field may sit in a closed tab or further down the page).
     * The field errors are still shown as usual.
     */
    public function exception(\Throwable $e, callable $stopPropagation): void
    {
        if ($e instanceof ValidationException) {
            $this->toast('error', __('admin.common.validation_failed', ['error' => collect($e->errors())->flatten()->first()]));
        }
    }

    /**
     * Toast shown on the next page (after a redirect / wire:navigate).
     */
    protected function flashToast(string $type, string $message): void
    {
        session()->flash('toast', ['type' => $type, 'message' => $message]);
    }
}
