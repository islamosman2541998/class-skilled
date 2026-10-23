<?php

namespace App\Policies;

use App\Models\User;

/**
 * Users are managed with "manage users". These rules also bind administrators
 * (Gate::before skips User abilities): nobody deletes themselves and the
 * last active administrator cannot be removed or demoted.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage users');
    }

    public function view(User $user, User $model): bool
    {
        return $user->is($model) || $user->can('manage users');
    }

    public function create(User $user): bool
    {
        return $user->can('manage users');
    }

    public function update(User $user, User $model): bool
    {
        return $user->can('manage users');
    }

    public function delete(User $user, User $model): bool
    {
        return $user->can('manage users')
            && ! $user->is($model)
            && ! static::isLastAdmin($model);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('manage users');
    }

    /**
     * Would removing admin rights from (or deleting / disabling) this user leave no active admin?
     */
    public static function isLastAdmin(User $model): bool
    {
        if (! $model->hasRole('admin')) {
            return false;
        }

        return User::role('admin')->where('is_active', true)->whereKeyNot($model->getKey())->doesntExist();
    }
}
