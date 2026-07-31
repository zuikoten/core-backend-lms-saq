<?php

namespace Modules\Core\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property \Modules\Core\Models\AcademicYear $resource
 */
class AcademicYearResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'year_name' => $this->resource->year_name,
            'is_active' => $this->resource->is_active,
        ];
    }
}
