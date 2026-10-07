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
        Schema::create('payment_gateway_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->restrictOnDelete();
            $table->foreignId('payment_channel_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('external_id')->unique(); // yang KITA generate & kirim ke Xendit
            // (rename dari gateway_reference_id)
            $table->string('gateway_trx_id')->nullable(); // `id` invoice dari respons Xendit
            $table->text('invoice_url')->nullable(); // NEW — link checkout dari Xendit,
            // ini yang dikirim/ditampilkan ke orang tua
            $table->enum('status', ['pending', 'paid', 'expired', 'failed', 'cancelled'])->default('pending');
            $table->boolean('is_overpayment')->default(false);
            // Nominal kelebihan bayar (untuk refund/pengalihan) — boolean saja tidak cukup.
            $table->decimal('overpaid_amount', 12, 2)->default(0);
            $table->decimal('amount', 12, 2);
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('raw_request')->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamps();

            $table->index(['invoice_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateway_transactions');
    }
};
