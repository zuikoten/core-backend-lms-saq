<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel HISTORI, bukan status tunggal — 1 siswa bisa punya banyak
     * baris per tahun ajaran kalau pindah rombel. Baris dengan
     * moved_out_at IS NULL = rombel yang sedang aktif untuk siswa itu.
     * Aturan "cuma 1 baris aktif per siswa per tahun ajaran" DITEGAKKAN
     * DI LEVEL APLIKASI (TransferStudentAction), bukan lewat unique
     * constraint DB — MySQL tidak punya cara bersih untuk partial unique
     * index tanpa generated column.
     */
    public function up(): void
    {
        Schema::create('class_group_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_group_id')->constrained()->restrictOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->restrictOnDelete();
            $table->date('moved_at');
            $table->date('moved_out_at')->nullable();
            $table->foreignId('moved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_group_students');
    }
};
