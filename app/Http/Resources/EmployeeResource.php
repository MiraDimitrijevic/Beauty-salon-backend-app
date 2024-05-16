<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;


class EmployeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public static $wrap = 'employee';

    public function toArray($request)
    {
        $base64Image = base64_encode($this->resource->image_data);

        return [
            'profession' => $this->resource->profession,
            'file_name' => $this->resource->file_name,
            'mime_type' => $this->resource->mime_type,
            'image' => $base64Image,
            'user'=> new UserResource($this->resource->user)
        ];
    }
  

}