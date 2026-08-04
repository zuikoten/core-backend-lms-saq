<?php

namespace Modules\Academic\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Models\AcademicYear;
use Modules\Core\Models\Classroom;
use Modules\Core\Models\GradeLevel;

class ClassGroup extends Model
{
    protected $fillable = [
        'grade_level_id',
        'academic_year_id',
        'classroom_id',
        'name',
        'homeroom_teacher_id',
    ];

    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function studentHistory(): HasMany
    {
        return $this->hasMany(ClassGroupStudent::class);
    }

    public function reportCards(): HasMany
    {
        return $this->hasMany(ReportCard::class);
    }

    /**
     * Siswa yang SEDANG aktif di rombel ini (moved_out_at masih NULL).
     * Bukan property biasa — dipanggil sebagai method karena ini query,
     * bukan relasi Eloquent standar (tidak ada FK langsung dari Student).
     */
    public function activeStudents()
    {
        return $this->studentHistory()->whereNull('moved_out_at')->with('student');
    }
}
