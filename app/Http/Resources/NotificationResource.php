<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Mosab\Translation\Middleware\RequestLanguage;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = json_decode($this->data, true);
        Carbon::setLocale(RequestLanguage::$language);
        $date = "";
        $title = null;
        $message = null;
        $diff_days = $this->created_at->diff()->days;

        if($diff_days <= 3)
            $date = $this->created_at->diffForHumans();

        else {
            if($this->created_at->format('Y') == Carbon::now()->format('Y'))
                $date = $this->created_at->format('d M');

            else
                $date = $this->created_at->format('d M Y');
        }
        
        if($this->type == 'from_admin'){
            $title = $data['title'][RequestLanguage::$language];
            $message = $data['body'][RequestLanguage::$language];
        } else {
            $title = $this->resource->get_title(RequestLanguage::$language);
            $message = $this->resource->get_message(RequestLanguage::$language);
        }

        return [
            'id'               => $this->id,
            'icon'             => $this->get_icon(),
            'type'             =>  __("notificationTypes.".$this->type, [], RequestLanguage::$language),
            'title'            => $title,
            'message'          => $message,
            'data'             => $data,
            'destination_type' => $this->get_destination_type(),
            'destination_id'   => $this->get_destination_id(),
            'date'             => $date,
        ];
    }
}

