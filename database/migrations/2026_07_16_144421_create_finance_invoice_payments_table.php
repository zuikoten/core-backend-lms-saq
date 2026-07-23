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
        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->restrictOnDelete();
            $table->foreignId('payment_channel_id')->constrained()->restrictOnDelete();
            $table->foreignId('payment_gateway_transaction_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('reference_number')->nullable(); // No. struk / transaction ID
            $table->decimal('amount_paid', 12, 2);
            $table->dateTime('paid_at');
            $table->foreignId('handover_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_payments');
    }
};
