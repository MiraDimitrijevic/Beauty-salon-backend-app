<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class AppointmentItemResource extends JsonResource
{
    public static $wrap = 'item';
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'date'=>$this->resource->appointment->date,
            'start_time'=>$this->resource->start_time,
            'end_time'=>$this->resource->end_time,
            'service'=>new OfferResource($this->resource->offer),
        ];    
    }
}

