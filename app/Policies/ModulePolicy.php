<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Permission-based policy: abilities map to "{action} {module}" permissions
 * (App\Support\Permissions). Admins pass through Gate::before.
 */
abstract class ModulePolicy
{
    abstract protected function module(): string;

    public function viewAny(User $user): bool
    {
        return $user->can('view '.$this->module());
    }

    public function view(User $user, ?Model $model = null): bool
    {
        return $user->can('view '.$this->module());
    }

    public function create(User $user): bool
    {
        return $user->can('create '.$this->module());
    }

    public function update(User $user, ?Model $model = null): bool
    {
        return $user->can('update '.$this->module());
    }

    public function delete(User $user, ?Model $model = null): bool
    {
        return $user->can('delete '.$this->module());
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete '.$this->module());
    }

    public function restore(User $user, ?Model $model = null): bool
    {
        return $user->can('delete '.$this->module());
    }

    public function forceDelete(User $user, ?Model $model = null): bool
    {
        return $user->can('delete '.$this->module());
    }
}
