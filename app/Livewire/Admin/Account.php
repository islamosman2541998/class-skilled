<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\WithToasts;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * My account: profile, password, dashboard language and two-factor (TOTP)
 * setup through Fortify's actions. Sensitive actions ask for the current password.
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

    public string $twoFactorPassword = '';

    public string $code = '';

    public bool $showingRecoveryCodes = false;

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

    public function enableTwoFactor(EnableTwoFactorAuthentication $enable): void
    {
        if (! $this->passwordConfirmed()) {
            return;
        }

        $enable($this->user());
        $this->reset('code');
    }

    public function confirmTwoFactor(ConfirmTwoFactorAuthentication $confirm): void
    {
        $this->validate(['code' => ['required', 'string']], [], ['code' => __('admin.account.confirm_code')]);

        try {
            $confirm($this->user(), $this->code);
        } catch (ValidationException) {
            $this->addError('code', __('admin.account.invalid_code'));

            return;
        }

        $this->reset('code', 'twoFactorPassword');
        $this->showingRecoveryCodes = true;
        $this->toast('success', __('admin.account.enabled_toast'));
    }

    public function disableTwoFactor(DisableTwoFactorAuthentication $disable): void
    {
        if (! $this->passwordConfirmed()) {
            return;
        }

        $disable($this->user());
        $this->reset('twoFactorPassword', 'showingRecoveryCodes');
        $this->toast('success', __('admin.account.disabled_toast'));
    }

    public function regenerateRecoveryCodes(GenerateNewRecoveryCodes $generate): void
    {
        if (! $this->passwordConfirmed()) {
            return;
        }

        $generate($this->user());
        $this->showingRecoveryCodes = true;
    }

    public function showRecoveryCodes(): void
    {
        if (! $this->passwordConfirmed()) {
            return;
        }

        $this->showingRecoveryCodes = true;
    }

    private function passwordConfirmed(): bool
    {
        $this->resetErrorBag('twoFactorPassword');

        if (! Hash::check($this->twoFactorPassword, $this->user()->password)) {
            $this->addError('twoFactorPassword', __('validation.current_password'));

            return false;
        }

        return true;
    }

    public function render(): View
    {
        $user = $this->user()->fresh();

        return view('livewire.admin.account', [
            'user' => $user,
            'pending' => $user?->two_factor_secret !== null && $user->two_factor_confirmed_at === null,
            'enabled' => (bool) $user?->hasTwoFactorEnabled(),
            'qr' => $user?->two_factor_secret ? $user->twoFactorQrCodeSvg() : null,
            'secret' => $user?->two_factor_secret ? decrypt($user->two_factor_secret) : null,
            'recoveryCodes' => $this->showingRecoveryCodes && $user?->two_factor_recovery_codes ? $user->recoveryCodes() : [],
        ])->title(__('admin.account.title'));
    }
}
