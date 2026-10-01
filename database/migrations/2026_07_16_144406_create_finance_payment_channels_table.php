<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_channels', function (Blueprint $table) {
            $table->id();
            $table->enum('channel_type', ['bank_transfer', 'virtual_account', 'e_wallet', 'cash', 'gateway']);
            // + 'gateway' → dipakai khusus untuk baris "Xendit" (hosted checkout campuran,
            //   tidak representasikan 1 metode spesifik seperti value lain)
            $table->string('name'); // BCA, Mandiri, OVO, Cash di Sekolah, Xendit, dll.
            $table->string('account_number')->nullable();
            $table->string('account_holder_name')->nullable();
            $table->string('provider')->default('manual'); // 'manual' atau 'xendit'
            $table->string('provider_channel_code')->nullable(); // dibiarkan ada untuk masa depan
            // (mis. kalau nanti nambah VA per-bank spesifik dari Xendit), tapi utk
            // Invoice API sekarang selalu null
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_channels');
    }
};
