<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Auth\Models\OtpCode;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    Permission::firstOrCreate(['name' => 'panel.access', 'guard_name' => 'web']);
});

test('staf bisa reset password lewat OTP 3-step lalu login pakai password baru', function () {
    $user = User::factory()->create(['password' => bcrypt('password-lama')]);
    $user->givePermissionTo('panel.access');

    // Step 1: minta OTP
    $this->post('/forgot-password/otp', [
        'phone_number' => $user->phone_number,
    ])->assertRedirect();

    $otp = OtpCode::where('user_id', $user->id)
        ->where('action_type', 'reset_password')
        ->latest('created_at')
        ->first();

    expect($otp)->not->toBeNull();

    // Step 2: verifikasi OTP
    $this->post('/forgot-password/otp/verify', [
        'phone_number' => $user->phone_number,
        'otp_code' => $otp->otp_code,
    ])->assertRedirect(route('password.otp.new-password.form', ['phone_number' => $user->phone_number]));

    // Step 3: set password baru
    $this->post('/forgot-password/otp/new-password', [
        'phone_number' => $user->phone_number,
        'password' => 'password-baru-123',
        'password_confirmation' => 'password-baru-123',
    ])->assertRedirect(route('login'));

    // Login pakai password baru harus berhasil
    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password-baru-123',
    ]);

    $response->assertRedirect(route('staff.dashboard'));
    $this->assertAuthenticatedAs($user->fresh());
});

test('tidak bisa langsung buka halaman set password baru tanpa verifikasi OTP dulu', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('panel.access');

    $response = $this->get('/forgot-password/otp/new-password?phone_number='.$user->phone_number);

    $response->assertRedirect(route('password.request.otp'));
});