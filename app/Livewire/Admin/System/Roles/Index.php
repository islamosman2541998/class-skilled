<?php

namespace App\Livewire\Admin\System\Roles;

use App\Livewire\Admin\Concerns\WithToasts;
use App\Support\Permissions;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permission matrix per role (editor, sales). The admin role is fixed.
 * Only administrators can change it.
 */
#[Layout('layouts.admin')]
class Index extends Component
{
    use WithToasts;

    public string $role = 'editor';

    /** @var array<string, bool> */
    public array $grants = [];

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);
        $this->loadRole();
    }

    public function updatedRole(): void
    {
        if (! in_array($this->role, ['editor', 'sales'], true)) {
            $this->role = 'editor';
        }

        $this->loadRole();
    }

    private function loadRole(): void
    {
        $granted = Role::findByName($this->role, 'web')->permissions->pluck('name')->all();
        $this->grants = [];

        foreach (Permissions::all() as $permission) {
            $this->grants[$this->key($permission)] = in_array($permission, $granted, true);
        }
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        $permissions = array_values(array_filter(
            Permissions::all(),
            fn (string $permission) => (bool) ($this->grants[$this->key($permission)] ?? false),
        ));

        Role::findByName($this->role, 'web')->syncPermissions($permissions);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        activity()->causedBy(auth()->user())->withProperties(['role' => $this->role, 'permissions' => $permissions])->event('updated')->log('Role');
        $this->dispatch('form-saved');
        $this->toast('success', __('admin.roles_screen.saved'));
    }

    /**
     * Permission names contain spaces; Livewire keys use dashes.
     */
    public function key(string $permission): string
    {
        return str_replace(' ', '-', $permission);
    }

    public function render(): View
    {
        $counts = Role::query()->withCount('users')->pluck('users_count', 'name');

        return view('livewire.admin.system.roles.index', [
            'modules' => [...Permissions::CONTENT_MODULES, ...Permissions::REQUEST_MODULES],
            'actions' => Permissions::ACTIONS,
            'special' => Permissions::SPECIAL,
            'counts' => $counts,
        ])->title(__('admin.roles_screen.title'));
    }
}
