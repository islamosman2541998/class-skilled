<?php

namespace App\Livewire\Admin\System\Users;

use App\Livewire\Admin\Concerns\WithToasts;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Form extends Component
{
    use WithToasts;

    public ?User $user = null;

    public string $name = '';

    public string $email = '';

    public string $role = 'editor';

    public string $locale = 'ar';

    public bool $is_active = true;

    public bool $force_two_factor = false;

    public string $password = '';

    public function mount(?User $user = null): void
    {
        if ($user?->exists) {
            $this->authorize('update', $user);
            $this->user = $user;
            $this->fill($user->only(['name', 'email', 'locale', 'is_active', 'force_two_factor']));
            $this->role = $user->getRoleNames()->first() ?? 'editor';
        } else {
            $this->authorize('create', User::class);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($this->user?->id)],
            'role' => ['required', Rule::in(['admin', 'editor', 'sales'])],
            'locale' => ['required', Rule::in(['ar', 'en'])],
            'is_active' => ['boolean'],
            'force_two_factor' => ['boolean'],
            'password' => [$this->user ? 'nullable' : 'required', 'string', PasswordRule::min(10)->letters()->numbers()],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'name' => __('admin.users.name'),
            'email' => __('admin.users.email'),
            'role' => __('admin.users.role'),
            'locale' => __('admin.users.locale'),
            'password' => __('admin.users.password'),
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->user && UserPolicy::isLastAdmin($this->user) && ($data['role'] !== 'admin' || ! $data['is_active'])) {
            $this->addError('role', __('admin.users.last_admin'));

            return;
        }

        if ($this->user && $this->user->is(auth()->user()) && ! $data['is_active']) {
            $this->addError('is_active', __('admin.users.cannot_delete_self'));

            return;
        }

        $attributes = collect($data)->only(['name', 'email', 'locale', 'is_active', 'force_two_factor'])->all();

        if (filled($data['password'])) {
            $attributes['password'] = $data['password'];
        }

        $user = $this->user ?? new User;
        $user->fill($attributes);

        if (! $user->exists) {
            $user->email_verified_at = now();
        }

        $user->save();
        $user->syncRoles([$data['role']]);

        $this->dispatch('form-saved');
        $this->flashToast('success', $this->user ? __('admin.common.saved') : __('admin.common.created'));
        $this->redirectRoute('admin.users.index', navigate: true);
    }

    public function sendResetLink(): void
    {
        abort_unless($this->user !== null, 404);
        $this->authorize('update', $this->user);

        Password::broker()->sendResetLink(['email' => $this->user->email]);
        $this->toast('success', __('admin.users.reset_sent'));
    }

    public function render(): View
    {
        $title = $this->user ? __('admin.users.edit') : __('admin.users.create');

        return view('livewire.admin.system.users.form', [
            'title' => $title,
            'roles' => collect(['admin', 'editor', 'sales'])->mapWithKeys(fn ($r) => [$r => __('admin.roles.'.$r)])->all(),
        ])->title($title);
    }
}
