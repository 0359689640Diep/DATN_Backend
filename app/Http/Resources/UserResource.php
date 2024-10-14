<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id, // Trả về ID
            'name' => $this->name, // Trả về tên
            'email' => $this->email, // Trả về email
            "image" => url('storage/' . $this->image),
            "phone"  => $this-> phone, 
            "address"  => $this-> address,
        ];
    }
}
