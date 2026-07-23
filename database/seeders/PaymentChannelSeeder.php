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
            ],
            [
                'channel_type' => 'bank_transfer',
                'name' => 'BCA',
                'account_number' => '1234567890', // ganti sesuai rekening sekolah asli
                'account_holder_name' => 'Yayasan/Sekolah TK ...', // ganti sesuai nama rekening
            ],
            [
                'channel_type' => 'bank_transfer',
                'name' => 'Mandiri',
                'account_number' => '0987654321', // ganti sesuai rekening sekolah asli
                'account_holder_name' => 'Yayasan/Sekolah TK ...', // ganti sesuai nama rekening
            ],
        ];

        foreach ($channels as $channel) {
            PaymentChannel::firstOrCreate(
                ['channel_type' => $channel['channel_type'], 'name' => $channel['name']],
                $channel
            );
        }
    }
}
