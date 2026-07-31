<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    Permission::firstOrCreate(['name' => 'panel.access', 'guard_name' => 'web']);
});

test('staf bisa login dengan email dan password yang benar', function () {
    $user = User::factory()->create([
        'email' => 'staff@sekolah.test',
        'password' => bcrypt('password-benar'),
    ]);
    $user->givePermissionTo('panel.access');

    $response = $this->post('/login', [
        'email' => 'staff@sekolah.test',
        'password' => 'password-benar',
    ]);

    $response->assertRedirect(route('staff.dashboard'));
    $this->assertAuthenticatedAs($user);
});

test('staf tidak bisa login dengan password salah', function () {
    User::factory()->create([
        'email' => 'staff@sekolah.test',
        'password' => bcrypt('password-benar'),
    ])->givePermissionTo('panel.access');

    $response = $this->post('/login', [
        'email' => 'staff@sekolah.test',
        'password' => 'password-salah',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('staf tanpa permission panel.access ditolak walau password benar', function () {
    // Sengaja TIDAK diberi panel.access
    User::factory()->create([
        'email' => 'staff@sekolah.test',
        'password' => bcrypt('password-benar'),
    ]);

    $response = $this->post('/login', [
        'email' => 'staff@sekolah.test',
        'password' => 'password-benar',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});
