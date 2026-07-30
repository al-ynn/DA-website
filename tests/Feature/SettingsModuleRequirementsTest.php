<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

function settingsRequirementsAdmin(): User
{
    return User::create([
        'first_name' => 'System',
        'last_name' => 'Administrator',
        'email' => 'settings-admin@example.test',
        'password' => 'password',
        'role' => 'admin',
        'is_draft' => false,
        'is_disabled' => false,
    ]);
}

test('new accounts receive the role based temporary password as a secure hash', function () {
    $this->actingAs(settingsRequirementsAdmin());

    $roles = [
        'admin' => 'Admin123',
        'chemist' => 'Chemist123',
        'agriculturist' => 'Agriculturist123',
    ];

    foreach ($roles as $role => $temporaryPassword) {
        $response = $this->post(route('user-management.store'), [
            'first_name' => ucfirst($role),
            'last_name' => 'Account',
            'sex' => 'MALE',
            'birthdate' => '1990-01-01',
            'contact_number' => '+63917'.str_pad((string) count(User::all()), 7, '0', STR_PAD_LEFT),
            'email' => "{$role}-account@example.test",
            'role' => $role,
            'additional_tasks' => [],
            'is_disabled' => false,
            'is_draft' => false,
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertSessionHas('temporary_password', $temporaryPassword);

        $user = User::where('email', "{$role}-account@example.test")->firstOrFail();

        expect($user->password)->not->toBe($temporaryPassword)
            ->and(Hash::check($temporaryPassword, $user->password))->toBeTrue();
    }
});

test('templates settings page is accessible through its named route', function () {
    $this->actingAs(settingsRequirementsAdmin())
        ->get(route('templates.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/templates'));
});

test('all five settings pages render through their existing settings routes', function () {
    $this->actingAs(settingsRequirementsAdmin());

    $pages = [
        'profile.edit' => 'settings/Profile',
        'user-password.edit' => 'settings/Password',
        'appearance.edit' => 'settings/Appearance',
        'templates.index' => 'settings/templates',
    ];

    foreach ($pages as $routeName => $component) {
        $this->get(route($routeName))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component($component));
    }

    $this->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('two-factor.show'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/TwoFactor'));
});
