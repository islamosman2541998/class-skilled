<?php

use App\Livewire\Admin\Account;
use App\Livewire\Admin\AssetField;
use App\Livewire\Admin\AssetPicker;
use App\Livewire\Admin\CommandPalette;
use App\Livewire\Admin\System\ActivityLog;
use App\Livewire\Admin\System\Roles\Index as RolesIndex;
use App\Livewire\Admin\System\Users\Form as UserForm;
use App\Livewire\Admin\System\Users\Index as UsersIndex;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use PragmaRX\Google2FA\Google2FA;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

test('an active user can sign in and lands on the dashboard', function () {
    $user = userWithRole('admin', ['email' => 'admin@example.test']);

    $this->post(route('login.store'), ['email' => 'admin@example.test', 'password' => 'password'])
        ->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($user);
    expect($user->fresh()->last_login_at)->not->toBeNull();

    $this->get(route('admin.dashboard'))->assertOk()->assertSee(__('admin.panel_name'));
});

test('inactive users cannot sign in and are signed out', function () {
    $user = userWithRole('editor', ['email' => 'off@example.test', 'is_active' => false]);

    $this->post(route('login.store'), ['email' => 'off@example.test', 'password' => 'password'])
        ->assertSessionHasErrors('email');
    $this->assertGuest();

    $this->actingAs($user)->get(route('admin.dashboard'))->assertRedirect(route('login'));
    $this->assertGuest();
});

test('login is throttled after five failed attempts', function () {
    userWithRole('admin', ['email' => 'admin@example.test']);
    RateLimiter::clear('admin@example.test|127.0.0.1');

    foreach (range(1, 5) as $attempt) {
        $this->post(route('login.store'), ['email' => 'admin@example.test', 'password' => 'wrong']);
    }

    $this->post(route('login.store'), ['email' => 'admin@example.test', 'password' => 'password'])
        ->assertStatus(429);
    $this->assertGuest();
});

test('users with two-factor enabled must pass the challenge', function () {
    $user = userWithRole('admin', ['email' => 'tfa@example.test']);
    $secret = app(Google2FA::class)->generateSecretKey();
    $user->forceFill([
        'two_factor_secret' => encrypt($secret),
        'two_factor_recovery_codes' => encrypt(json_encode(['recovery-1'])),
        'two_factor_confirmed_at' => now(),
    ])->save();

    $this->post(route('login.store'), ['email' => 'tfa@example.test', 'password' => 'password'])
        ->assertRedirect(route('two-factor.login'));
    $this->assertGuest();

    $this->get(route('two-factor.login'))->assertOk()->assertSee(__('admin.auth.challenge_title'));

    $this->post(route('two-factor.login.store'), ['code' => app(Google2FA::class)->getCurrentOtp($secret)])
        ->assertRedirect(route('admin.dashboard'));
    $this->assertAuthenticatedAs($user);
});

test('permissions are enforced per role on dashboard routes', function () {
    $sales = userWithRole('sales');
    $editor = userWithRole('editor');
    $admin = userWithRole('admin');

    $this->actingAs($sales)->get(route('admin.dashboard'))->assertOk();
    $this->actingAs($sales)->get(route('admin.users.index'))->assertForbidden();
    $this->actingAs($editor)->get(route('admin.users.index'))->assertForbidden();
    $this->actingAs($editor)->get(route('admin.activity.index'))->assertForbidden();
    $this->actingAs($editor)->get(route('admin.roles.index'))->assertForbidden();
    $this->actingAs($admin)->get(route('admin.users.index'))->assertOk();
    $this->actingAs($admin)->get(route('admin.roles.index'))->assertOk();
    $this->actingAs($admin)->get(route('admin.activity.index'))->assertOk();
    $this->actingAs($admin)->get(route('admin.account'))->assertOk();
});

test('the sidebar only lists screens the user may open', function () {
    $this->actingAs(userWithRole('sales'))->get(route('admin.dashboard'))
        ->assertOk()
        ->assertDontSee(route('admin.users.index'));

    $this->actingAs(userWithRole('admin'))->get(route('admin.dashboard'))
        ->assertSee(route('admin.users.index'));
});

test('the users table searches, filters, sorts and exports', function () {
    $admin = userWithRole('admin', ['name' => 'Zed Admin']);
    userWithRole('editor', ['name' => 'Alice Editor', 'email' => 'alice@example.test']);
    userWithRole('sales', ['name' => 'Bob Sales', 'is_active' => false]);

    $this->actingAs($admin);

    Livewire::test(UsersIndex::class)
        ->assertSee('Alice Editor')->assertSee('Bob Sales')
        ->set('search', 'alice')
        ->assertSee('Alice Editor')->assertDontSee('Bob Sales')
        ->set('search', '')
        ->call('setFilter', 'role', 'sales')
        ->assertSee('Bob Sales')->assertDontSee('Alice Editor')
        ->call('clearFilters')
        ->call('setFilter', 'active', '0')
        ->assertSee('Bob Sales')->assertDontSee('Alice Editor')
        ->call('clearFilters')
        ->assertSeeInOrder(['Alice Editor', 'Bob Sales', 'Zed Admin'])
        ->call('sortBy', 'name')
        ->assertSeeInOrder(['Zed Admin', 'Bob Sales', 'Alice Editor'])
        ->call('export')
        ->assertFileDownloaded();
});

test('users cannot delete themselves and the last admin cannot be demoted', function () {
    User::role('admin')->delete();
    $admin = userWithRole('admin');
    $this->actingAs($admin);

    Livewire::test(UsersIndex::class)->call('delete', $admin->id);
    expect(User::find($admin->id))->not->toBeNull();

    Livewire::test(UserForm::class, ['user' => $admin])
        ->set('role', 'editor')
        ->call('save')
        ->assertHasErrors('role');
    expect($admin->fresh()->hasRole('admin'))->toBeTrue();

    $other = userWithRole('editor');
    Livewire::test(UsersIndex::class)->call('delete', $other->id);
    expect(User::find($other->id))->toBeNull();
});

test('admins create users with a role', function () {
    $this->actingAs(userWithRole('admin'));

    Livewire::test(UserForm::class)
        ->set('name', 'New Sales')
        ->set('email', 'new@example.test')
        ->set('role', 'sales')
        ->set('password', 'secret-pass-123')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.users.index'));

    expect(User::where('email', 'new@example.test')->first()?->hasRole('sales'))->toBeTrue();
});

test('role permissions can be changed by admins', function () {
    $this->actingAs(userWithRole('admin'));

    Livewire::test(RolesIndex::class)
        ->set('role', 'sales')
        ->set('grants.view-projects', true)
        ->call('save');

    expect(Role::findByName('sales')->hasPermissionTo('view projects'))->toBeTrue();
});

test('the activity log lists changes with a diff', function () {
    $admin = userWithRole('admin');
    $this->actingAs($admin);
    $admin->update(['name' => 'Renamed Admin']);

    Livewire::test(ActivityLog::class)
        ->assertSee('Renamed Admin')
        ->call('show', Activity::latest('id')->value('id'))
        ->assertSee('Renamed Admin');
});

test('two-factor can be enabled from my account', function () {
    $user = userWithRole('editor');
    $this->actingAs($user);

    Livewire::test(Account::class)
        ->set('twoFactorPassword', 'wrong')
        ->call('enableTwoFactor')
        ->assertHasErrors('twoFactorPassword')
        ->set('twoFactorPassword', 'password')
        ->call('enableTwoFactor')
        ->assertHasNoErrors();

    $user->refresh();
    expect($user->two_factor_secret)->not->toBeNull();

    Livewire::test(Account::class)
        ->set('code', app(Google2FA::class)->getCurrentOtp(decrypt($user->two_factor_secret)))
        ->call('confirmTwoFactor')
        ->assertHasNoErrors();

    expect($user->fresh()->hasTwoFactorEnabled())->toBeTrue();
});

test('the asset picker returns the chosen ids to the field', function () {
    $this->actingAs(userWithRole('editor'));

    Livewire::test(AssetPicker::class)
        ->call('openPicker', 'af-test', true, 'image', [])
        ->assertSet('open', true)
        ->call('toggle', 5)
        ->call('toggle', 7)
        ->call('confirm')
        ->assertDispatched('asset-picked', target: 'af-test', ids: [5, 7]);

    $field = Livewire::test(AssetField::class, ['multiple' => true]);
    $field->call('picked', $field->get('fieldId'), [3, 4])->assertSet('value', [3, 4])
        ->call('reorder', [4, 3])->assertSet('value', [4, 3])
        ->call('remove', 4)->assertSet('value', [3]);
});

test('the command palette is permission aware', function () {
    $this->actingAs(userWithRole('sales'));

    Livewire::test(CommandPalette::class)
        ->set('query', 'us')
        ->assertDontSee(__('admin.nav.users'));

    $this->actingAs(userWithRole('admin'));

    Livewire::test(CommandPalette::class)
        ->set('query', mb_substr(__('admin.nav.users'), 0, 4))
        ->assertSee(__('admin.nav.users'));
});
