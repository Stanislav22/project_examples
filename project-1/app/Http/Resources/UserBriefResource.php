<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\MediaResource;

class UserBriefResource extends JsonResource
{
    /**
     * @inheritdoc
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_api' => $this->is_api,
            'image' => $this->when(! $this->is_api && $this->hasPicture(), function() {
                return new MediaResource($this->getPicture());
            }),
        ];
    }
}