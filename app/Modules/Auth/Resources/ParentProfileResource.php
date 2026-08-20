<?php

namespace Modules\Auth\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @property \App\Models\User $resource
 */
class ParentProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'username' => $this->resource->username,
            'email' => $this->resource->email,
            'phone_number' => $this->resource->phone_number,
            'avatar_url' => $this->resource->avatar ? Storage::url($this->resource->avatar) : null,
            'is_active' => $this->resource->is_active,
        ];
    }
}
