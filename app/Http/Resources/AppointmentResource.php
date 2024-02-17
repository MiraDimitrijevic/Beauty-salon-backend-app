<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\AppointmentItemResource;

class AppointmentResource extends JsonResource
{

    public static $wrap = 'appointment';
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'date'=>$this->resource->date,
            'start_time'=>$this->resource->start_time,
            'end_time'=>$this->resource->end_time,
            'cost'=>$this->resource->cost,
            'canceled'=>$this->resource->canceled,
            'services' => $this->resource->appointmentItem,         
        ];
    }
}
