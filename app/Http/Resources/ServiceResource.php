<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public static $wrap='service';
    public function toArray($request)
    {
        return [
            'id'=>$this->resource->id,
            'name'=>$this->resource->name,
            'description'=>$this->resource->description,
            'cost'=>$this->resource->cost,
            'duration'=>$this->resource->duration
        ];
    }
}
