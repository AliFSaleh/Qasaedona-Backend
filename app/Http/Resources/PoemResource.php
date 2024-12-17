<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PoemResource extends JsonResource
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
            'created_by'    => $this->created_by,
            'title'         => $this->title,
            'type'          => $this->type,
            'body'          => $this->body,
            'audios_count'  => $this->audios_count,
            'status'        => $this->status,
            'user'          => new UserResource($this->whenLoaded('user')),
            'poet'          => new UserResource($this->whenLoaded('poet')),
            'poem_type'     => new PoemAttributeResource($this->whenLoaded('poem_type')),
            'category'      => new PoemAttributeResource($this->whenLoaded('category')),
            'language'      => new PoemAttributeResource($this->whenLoaded('language')),
            'occasion'      => new OccasionResource($this->whenLoaded('occasion')),
            'created_at'    => $this->created_at,
        ];
    }
}
