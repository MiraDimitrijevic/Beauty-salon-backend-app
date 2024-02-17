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
        $imageURL = $this->resource->imageURL;
        if (Storage::disk('public')->exists($imageURL)) {
        $imageData = Storage::disk('public')->get($imageURL);
 
        return [
            'profession' => $this->resource->profession,
            'imageURL' => 'data:image/png;base64,' . base64_encode($imageData),
            'user'=> new UserResource($this->resource->user)
        ];
    }
    else {
        return [
            'profession' => $this->resource->profession,
            'user'=> new UserResource($this->resource->user)
        ];
    }
    }
}