<?php

namespace App\Http\Resources;

use App\Models\Lesson;
use App\Models\Rawaded;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SliderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $linked_type = $this->linked_type;

        if($this->type == 'internal_linked'){
            if($request->linked_type == 'radod')
                $linked_type = Rawaded::class;
            if($request->linked_type == 'poet')
                $linked_type = User::class;
            if($request->linked_type == 'lesson')
                $linked_type = Lesson::class;
        }
        return [
            'id'              => $this->id,
            'image'           => $this->image,
            'linked_type'     => $linked_type,
            'linked_id'       => $this->linked_id,
            'type'            => $this->type,
            'external_link'   => $this->external_link
        ];
    }
}
