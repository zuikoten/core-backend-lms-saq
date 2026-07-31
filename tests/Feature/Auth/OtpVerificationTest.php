<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Auth\Models\OtpCode;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    Permission::firstOrCreate(['name' => 'panel.access', 'guard_name' => 'web']);
});

test('OTP yang sudah kedaluwarsa ditolak', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('panel.access');

    OtpCode::create([
        'user_id' => $user->id,
        'phone_number' => $user->phone_number,
        'otp_code' => '123456',
        'action_type' => 'login',
        'expires_at' => now()->subMinute(), // sudah lewat
        'is_used' => false,
        'attempts' => 0,
    ]);

    $response = $this->post('/login/otp/verify', [
        'phone_number' => $user->phone_number,
        'otp_code' => '123456',
    ]);

    $response->assertSessionHasErrors('otp_code');
    $this->assertGuest();
});

test('OTP hangus otomatis setelah 3x percobaan salah', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('panel.access');

    OtpCode::create([
        'user_id' => $user->id,
        'phone_number' => $user->phone_number,
        'otp_code' => '123456',
        'action_type' => 'login',
        'expires_at' => now()->addMinutes(5),
        'is_used' => false,
        'attempts' => 0,
    ]);

    // Salah 3x berturut-turut
    foreach (range(1, 3) as $_) {
        $this->post('/login/otp/verify', [
            'phone_number' => $user->phone_number,
            'otp_code' => '000000',
        ]);
    }

    // Percobaan ke-4 pakai kode yang BENAR — tetap harus ditolak
    $response = $this->post('/login/otp/verify', [
        'phone_number' => $user->phone_number,
        'otp_code' => '123456',
    ]);

    $response->assertSessionHasErrors('too_many_attempts');
    $this->assertGuest();
});