<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PoetryCollectionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            // 'created_by'    => $this->created_by,
            'title'         => $this->title,
            'description'   => $this->description,
            'status'        => $this->status,
            'poet'          => new UserResource($this->whenLoaded('poet')),
            'created_at'    => $this->created_at,
        ];
    }
}
