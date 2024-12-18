<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonResource extends JsonResource
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
            'body'          => $this->body,
            'status'        => $this->status,
            'poet'          => new UserResource($this->whenLoaded('poet')),
            'created_at'    => $this->created_at,
        ];
    }
}
