<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenjang_id')->constrained('jenjang')->cascadeOnDelete();
            $table->string('name'); // "TK-A", "TK-B", "Kelas 1", "Kelas 7", "Kelas 10", dst.
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['jenjang_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_levels');
    }
};