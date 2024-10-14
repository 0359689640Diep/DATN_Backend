<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
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
            'id' => $this->id,
            'person_in_charge' => $this->person_in_charge,
            'check_in_date' => Carbon::parse($this->check_in_date)->format('d-m-Y'), // Định dạng ngày dd-mm-yy
            'check_out_date' => Carbon::parse($this->check_out_date)->format('d-m-Y'), // Định dạng ngày dd-mm-yy
            'total_price' => number_format($this->total_price ?? 0), // Định dạng tiền tệ Việt Nam
            'created_at' => Carbon::parse($this->created_at)->format('d-m-Y'), // Định dạng ngày dd-mm-yy
            'room' => [
                'number' => $this->room ?->number,
            ],
            'room_type' => [
                'type' => $this->roomType->type,
            ],
            'status' => [
                'name' => $this->status->name,
                'color' => $this->status->color,
            ],
        ];
    }
}

