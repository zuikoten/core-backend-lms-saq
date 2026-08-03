<?php

namespace Modules\Student\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property \Modules\Student\Models\ParentProfile $resource
 */
class ParentProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'phone_number' => $this->resource->phone_number,
            'father_name' => $this->resource->father_name,
            'mother_name' => $this->resource->mother_name,
            'address' => $this->resource->address,
        ];
    }
}