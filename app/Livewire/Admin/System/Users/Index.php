<?php

namespace App\Livewire\Admin\System\Users;

use App\Livewire\Admin\Concerns\WithDataTable;
use App\Livewire\Admin\Concerns\WithToasts;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Index extends Component
{
    use WithDataTable;
    use WithToasts;

    public function mount(): void
    {
        $this->authorize('viewAny', User::class);
    }

    protected function baseQuery(): Builder
    {
        return User::query()->with('roles');
    }

    protected function applySearch(Builder $query, string $term): void
    {
        $like = '%'.$term.'%';
        $query->where('name', 'like', $like)->orWhere('email', 'like', $like);
    }

    protected function applyFilters(Builder $query): void
    {
        if (filled($this->filters['role'] ?? null)) {
            $role = (string) $this->filters['role'];
            $query->whereHas('roles', fn (Builder $q) => $q->where('name', $role));
        }

        if (($this->filters['active'] ?? '') !== '') {
            $query->where('is_active', $this->filters['active'] === '1');
        }
    }

    protected function sortableColumns(): array
    {
        return ['name' => 'name', 'email' => 'email', 'login' => 'last_login_at', 'created' => 'created_at'];
    }

    protected function defaultSort(): array
    {
        return ['name', 'asc'];
    }

    protected function exportName(): string
    {
        return 'users';
    }

    protected function exportColumns(): array
    {
        return [
            __('admin.users.name') => fn (Model $u) => $u->getAttribute('name'),
            __('admin.users.email') => fn (Model $u) => $u->getAttribute('email'),
            __('admin.users.role') => fn (Model $u) => $u instanceof User ? $u->getRoleNames()->map(fn ($r) => __('admin.roles.'.$r))->implode(', ') : '',
            __('admin.users.status') => fn (Model $u) => $u->getAttribute('is_active') ? __('admin.users.active') : __('admin.users.inactive'),
            __('admin.users.two_factor') => fn (Model $u) => $u instanceof User && $u->hasTwoFactorEnabled() ? __('admin.users.enabled') : __('admin.users.disabled'),
            __('admin.users.last_login') => fn (Model $u) => $u->getAttribute('last_login_at')?->format('Y-m-d H:i'),
        ];
    }

    protected function authorizeExport(): void
    {
        $this->authorize('viewAny', User::class);
    }

    public function toggleActive(int $id): void
    {
        $user = User::query()->findOrFail($id);
        $this->authorize('update', $user);

        if ($user->is(auth()->user()) || ($user->is_active && UserPolicy::isLastAdmin($user))) {
            $this->toast('error', $user->is(auth()->user()) ? __('admin.users.cannot_delete_self') : __('admin.users.last_admin'));

            return;
        }

        $user->update(['is_active' => ! $user->is_active]);
        $this->toast('success', __('admin.common.updated'));
    }

    public function delete(int $id): void
    {
        $user = User::query()->findOrFail($id);

        if (! auth()->user()?->can('delete', $user)) {
            $this->toast('error', $user->is(auth()->user()) ? __('admin.users.cannot_delete_self') : __('admin.users.last_admin'));

            return;
        }

        $user->delete();
        $this->clearSelection();
        $this->toast('success', __('admin.common.deleted'));
    }

    public function bulkSetActive(bool $active): void
    {
        $this->authorize('deleteAny', User::class);
        $skipped = 0;

        foreach ($this->selectedQuery()->get() as $user) {
            /** @var User $user */
            if (! $active && ($user->is(auth()->user()) || UserPolicy::isLastAdmin($user))) {
                $skipped++;

                continue;
            }

            $user->update(['is_active' => $active]);
        }

        $this->clearSelection();
        $this->toast($skipped ? 'warning' : 'success', $skipped ? __('admin.users.last_admin') : __('admin.common.updated'));
    }

    public function bulkDelete(): void
    {
        $this->authorize('deleteAny', User::class);
        $skipped = 0;

        foreach ($this->selectedQuery()->get() as $user) {
            if (auth()->user()?->can('delete', $user)) {
                $user->delete();
            } else {
                $skipped++;
            }
        }

        $this->clearSelection();
        $this->toast($skipped ? 'warning' : 'success', $skipped ? __('admin.users.last_admin') : __('admin.common.deleted'));
    }

    public function render(): View
    {
        return view('livewire.admin.system.users.index', [
            'roles' => collect(['admin', 'editor', 'sales'])->mapWithKeys(fn ($r) => [$r => __('admin.roles.'.$r)])->all(),
        ])->title(__('admin.users.title'));
    }
}
