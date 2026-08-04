<?php

namespace Modules\Academic\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property \Modules\Academic\Models\ReportCard $resource
 */
class ReportCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'semester' => $this->resource->semester->name,
            'academic_year' => $this->resource->semester->academicYear->year_name,
            'class_group' => $this->resource->classGroup->name,
            'summary_notes' => $this->resource->summary_notes,
            'published_at' => $this->resource->published_at?->format('Y-m-d'),
        ];
    }
}
