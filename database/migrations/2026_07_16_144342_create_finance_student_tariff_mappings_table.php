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
        Schema::create('student_tariff_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->restrictOnDelete();
            $table->foreignId('billing_tariff_id')->constrained()->restrictOnDelete();
            $table->foreignId('academic_year_id')->constrained()->restrictOnDelete();
            $table->foreignId('billing_type_id')->constrained()->restrictOnDelete();
            $table->timestamps();

            $table->unique(
                ['student_id', 'academic_year_id', 'billing_type_id'],
                'uq_student_tariff_per_type_per_year'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_tariff_mappings');
    }
};
