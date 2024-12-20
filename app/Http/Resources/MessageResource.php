<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'full_name'       => $this->full_name,
            'email'           => $this->email,
            'phone'           => $this->phone,
            'message'         => $this->message,
            'date'            => $this->date,
            'status'          => $this->status,
        ];
    }
}
