<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// The suite processes real images (GD) and exceeds the CLI default time limit on slow disks.
set_time_limit(0);

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/**
 * A dashboard user with the given role (admin, editor, sales).
 *
 * @param  array<string, mixed>  $attributes
 */
function userWithRole(string $role, array $attributes = []): User
{
    $user = User::factory()->create(array_merge(['is_active' => true, 'locale' => 'ar'], $attributes));
    $user->assignRole($role);

    return $user;
}
