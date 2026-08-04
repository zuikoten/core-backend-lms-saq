<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Academic\Models\ClassGroup;

class Classroom extends Model
{
    protected $fillable = [
        'name',
        'capacity',
        'location',
    ];

    public function classGroups(): HasMany
    {
        return $this->hasMany(ClassGroup::class);
    }
}
