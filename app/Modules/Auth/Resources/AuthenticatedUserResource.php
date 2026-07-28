<?php

namespace Modules\Auth\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property \App\Models\User $resource
 */
class AuthenticatedUserResource extends JsonResource
{
    /**
     * @param  string|null  $token  Plain text Sanctum token, di-set manual dari Controller
     *                               ($resource->withToken($token)) karena tidak tersimpan di kolom.
     */
    private ?string $token = null;

    public function withToken(string $token): static
    {
        $this->token = $token;

        return $this;
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'phone_number' => $this->resource->phone_number,
            'email' => $this->resource->email,
            'role' => $this->resource->getRoleNames()->first(),
            'token' => $this->token,
        ];
    }
}
