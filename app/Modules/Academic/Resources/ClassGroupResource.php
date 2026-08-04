<?php

namespace Modules\Academic\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property \Modules\Academic\Models\ClassGroup $resource
 */
class ClassGroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'grade_level' => $this->resource->gradeLevel->name,
            'jenjang' => $this->resource->gradeLevel->jenjang->name,
            'academic_year' => $this->resource->academicYear->year_name,
            'classroom' => $this->resource->classroom?->name,
        ];
    }
}
