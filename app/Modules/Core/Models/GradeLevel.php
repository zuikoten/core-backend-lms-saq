<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeLevel extends Model
{
    protected $fillable = [
        'jenjang_id',
        'name',
        'sort_order',
    ];

    public function jenjang(): BelongsTo
    {
        return $this->belongsTo(Jenjang::class);
    }

    // Relasi ke ClassGroup (modul Academic) menyusul begitu tabel
    // class_groups dibuat — GradeLevel akan jadi acuan jenjang/tingkat
    // rombel, direferensikan lewat grade_level_id.
}
