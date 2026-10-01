<?php

namespace Database\Seeders;

use Modules\Finance\Models\PaymentChannel;
use Illuminate\Database\Seeder;

class PaymentChannelSeeder extends Seeder
{
    public function run(): void
    {
        $channels = [
            [
                'channel_type' => 'cash',
                'name' => 'Cash di Sekolah',
                'account_number' => null,
                'account_holder_name' => null,
                'provider' => 'manual',
                'provider_channel_code' => null,
                'is_active' => true,
            ],
            [
                'channel_type' => 'bank_transfer',
                'name' => 'BCA',
                'account_number' => '1234567890', // ganti sesuai rekening sekolah asli
                'account_holder_name' => 'Yayasan/Sekolah TK ...', // ganti sesuai nama rekening
                'provider' => 'manual',
                'provider_channel_code' => null,
                'is_active' => true,
            ],
            [
                'channel_type' => 'bank_transfer',
                'name' => 'Mandiri',
                'account_number' => '0987654321', // ganti sesuai rekening sekolah asli
                'account_holder_name' => 'Yayasan/Sekolah TK ...', // ganti sesuai nama rekening
                'provider' => 'manual',
                'provider_channel_code' => null,
                'is_active' => true,
            ],
            // Data Baru: Gateway Xendit
            [
                'channel_type' => 'gateway',
                'name' => 'Xendit',
                'account_number' => null,
                'account_holder_name' => null,
                'provider' => 'xendit',
                'provider_channel_code' => null,
                'is_active' => true,
            ],
        ];

        foreach ($channels as $channel) {
            // Menggunakan kombinasi 'provider' dan 'name' sebagai key unik agar lebih aman jika ke depan ada tipe channel yang sama
            PaymentChannel::firstOrCreate(
                [
                    'provider' => $channel['provider'],
                    'name' => $channel['name']
                ],
                $channel
            );
        }
    }
}
