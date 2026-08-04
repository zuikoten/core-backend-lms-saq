<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_level_id')->constrained()->restrictOnDelete();
            $table->foreignId('academic_year_id')->constrained()->restrictOnDelete();
            $table->foreignId('classroom_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name'); // "Kelas Kupu-kupu", "Kelas Melati", "Kelas 3A", "Kelas 3B", 

            // Belum FK — tabel teachers belum ada. Ditambahkan sebagai FK
            // constraint nanti begitu modul Teacher digarap (additive,
            // lewat ALTER TABLE, tidak mengubah data yang sudah ada).
            $table->unsignedBigInteger('homeroom_teacher_id')->nullable();

            $table->timestamps();

            $table->unique(['grade_level_id', 'academic_year_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_groups');
    }
};