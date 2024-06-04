<?php

namespace App\Http\Resources;

use App\Models\UserType;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Stores\Http\Resources\StoreBriefResource;

class UserResource extends JsonResource
{
    /**
     * @inheritdoc
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'is_api' => $this->is_api,
            'name' => $this->name,
            $this->mergeWhen(! $this->is_api, [
                'email' => $this->email,
                'mfa_method' => $this->mfa_method->value(),
            ]),
            'roles' => $this->roles->map(function ($value) {
                return $value->role->value();
            }),
            'is_active' => $this->is_active,
        ];
    }
}
