<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\WithToasts;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * My account: profile, dashboard language and password (the current password is required to change it).
 */
#[Layout('layouts.admin')]
class Account extends Component
{
    use WithToasts;

    public string $name = '';

    public string $email = '';

    public string $locale = 'ar';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        $user = $this->user();
        $this->fill($user->only(['name', 'email', 'locale']));
    }

    private function user(): User
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        return $user;
    }

    public function saveProfile(): void
    {
        $user = $this->user();
        $data = $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user->id)],
            'locale' => ['required', Rule::in(['ar', 'en'])],
        ]);

        $localeChanged = $data['locale'] !== $user->locale;
        $user->update($data);
        session()->put('admin_locale', $data['locale']);

        if ($localeChanged) {
            $this->flashToast('success', __('admin.common.saved'));
            $this->redirectRoute('admin.account', navigate: false);

            return;
        }

        $this->toast('success', __('admin.common.saved'));
    }

    public function savePassword(): void
    {
        $this->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(10)->letters()->numbers()],
        ], [], [
            'current_password' => __('admin.account.current_password'),
            'password' => __('admin.account.new_password'),
        ]);

        $this->user()->update(['password' => $this->password]);
        $this->reset('current_password', 'password', 'password_confirmation');
        $this->toast('success', __('admin.account.password_updated'));
    }

    public function render(): View
    {
        return view('livewire.admin.account')->title(__('admin.account.title'));
    }
}
