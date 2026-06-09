<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'role_name' => $this->role_name,
            'descriptions' => $this->descriptions,
            'permission_ids' => $this->getPermissionIds(),
            'created_at' => optional($this->created_at)->toDateTimeString(),
            'updated_at' => optional($this->updated_at)->toDateTimeString(),
        ];
    }

    private function getPermissionIds(): array
    {
        if ($this->relationLoaded('permissions')) {
            return $this->permissions->pluck('id')->values()->all();
        }

        return $this->permissions()->pluck('permissions.id')->values()->all();
    }
}
