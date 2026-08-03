<?php

namespace Modules\Student\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property \Modules\Student\Models\Student $resource
 */
class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'nisn' => $this->resource->nisn,
            'full_name' => $this->resource->full_name,
            'nickname' => $this->resource->nickname,
            'gender' => $this->resource->gender,
            'birth_date' => $this->resource->birth_date?->format('Y-m-d'),
            'status' => $this->resource->status,
            'parent' => new ParentProfileResource($this->whenLoaded('parentProfile')),
        ];
    }
}