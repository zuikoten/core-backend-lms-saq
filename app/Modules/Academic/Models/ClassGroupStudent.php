<?php

namespace Modules\Academic\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Models\AcademicYear;
use Modules\Student\Models\Student;

/**
 * Tabel HISTORI penempatan siswa ke rombel — 1 siswa bisa punya banyak
 * baris per tahun ajaran kalau pernah pindah rombel. Baris dengan
 * moved_out_at NULL = penempatan yang sedang aktif.
 */
class ClassGroupStudent extends Model
{
    protected $fillable = [
        'class_group_id',
        'student_id',
        'academic_year_id',
        'moved_at',
        'moved_out_at',
        'moved_by',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'moved_at' => 'date',
            'moved_out_at' => 'date',
        ];
    }

    public function classGroup(): BelongsTo
    {
        return $this->belongsTo(ClassGroup::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function movedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moved_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('moved_out_at');
    }
}
