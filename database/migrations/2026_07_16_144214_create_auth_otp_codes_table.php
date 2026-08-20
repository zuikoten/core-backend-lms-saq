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
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('phone_number')->nullable(); // diisi saat action_type = activation (nomor pendaftar) atau change_phone (nomor baru yang mau diverifikasi)
            $table->string('otp_code');
            $table->enum('action_type', ['login', 'activation', 'reset_password', 'change_phone']);
            $table->timestamp('expires_at');
            $table->boolean('is_used')->default(false);
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['phone_number', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
    }
};
