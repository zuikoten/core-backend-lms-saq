<?php

namespace App\Dashboard\Actions;

use Illuminate\Support\Facades\DB;

class GetAcademicSummaryAction
{
    /**
     * Ringkasan akademik tahun ajaran aktif: jumlah siswa per Grade Level
     * (untuk bar chart sederhana), dan keterisian tiap rombel dibanding
     * kapasitas ruang kelasnya (classrooms.capacity, nullable — rombel
     * tanpa classroom_id otomatis kapasitasnya null, ditandai "belum diatur"
     * di view, bukan dianggap penuh/kosong).
     */
    public function execute(): array
    {
        $academicYear = DB::table('academic_years')->where('is_active', true)->first();

        if (! $academicYear) {
            return ['per_grade_level' => [], 'keterisian_rombel' => []];
        }

        $perGradeLevel = DB::table('class_group_students')
            ->join('class_groups', 'class_groups.id', '=', 'class_group_students.class_group_id')
            ->join('grade_levels', 'grade_levels.id', '=', 'class_groups.grade_level_id')
            ->where('class_group_students.academic_year_id', $academicYear->id)
            ->whereNull('class_group_students.moved_out_at')
            ->selectRaw('grade_levels.name as nama_jenjang, COUNT(*) as jumlah_siswa')
            ->groupBy('grade_levels.name')
            ->get();

        $keterisianRombel = DB::table('class_groups')
            ->leftJoin('classrooms', 'classrooms.id', '=', 'class_groups.classroom_id')
            ->leftJoin('class_group_students', function ($join) {
                $join->on('class_group_students.class_group_id', '=', 'class_groups.id')
                    ->whereNull('class_group_students.moved_out_at');
            })
            ->where('class_groups.academic_year_id', $academicYear->id)
            ->selectRaw('class_groups.id, class_groups.name as nama_rombel, classrooms.capacity as kapasitas, COUNT(class_group_students.id) as jumlah_siswa')
            ->groupBy('class_groups.id', 'class_groups.name', 'classrooms.capacity')
            ->orderBy('class_groups.name')
            ->get();

        return [
            'per_grade_level' => $perGradeLevel->toArray(),
            'keterisian_rombel' => $keterisianRombel->toArray(),
        ];
    }
}
