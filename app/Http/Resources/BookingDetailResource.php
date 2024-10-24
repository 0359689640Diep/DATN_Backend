<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingDetailResource extends JsonResource
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
            'room_id' => $this->room_id,
            'room_type' => new RoomTypeResource($this->whenLoaded('roomType')),
            'status_color' => $this->status->color ?? null,
            'status_name' => $this->status->name ?? null,
            'person_in_charge' => $this->person_in_charge,
            'total_price' => number_format($this->total_price),
            'deposit_amount' => number_format($this->deposit_amount),
            'surcharge' => number_format($this->surcharge),
            'created_at' => Carbon::parse($this->created_at)->format('d-m-Y'), 
            
            // Xử lý nhiều payments
            'payments' => $this->payments->map(function($payment) {
                return [
                    'amount' => number_format($payment->amount),
                    'code' => $payment->code,
                    'payment_method' => $payment->payment_method === 1 ? "Tiền mặt" : "Chuyển khoản",
                    'payment_date' => Carbon::parse($payment->payment_date)->format('d-m-Y'),
                    'status_color' => $payment->status->color ?? null, // Kiểm tra status có tồn tại không
                    'status_name' => $payment->status->name ?? null,
                ];
            }),
        
            // Xử lý nhiều service_booking
            'service_booking' => $this->serviceBooking->map(function($serviceBooking) {
                return [
                    'id' => $serviceBooking->id,
                    'created_at' => Carbon::parse($serviceBooking->created_at)->format('d-m-Y'),
                    'quanlity_service' => $serviceBooking->quanlity_service,
                    'total_price' => number_format($serviceBooking->total_price),
                    'service_name' => $serviceBooking->service->name ?? null, // Kiểm tra service có tồn tại không
                    'status_color' => $serviceBooking->status->color ?? null,
                    'status_name' => $serviceBooking->status->name ?? null,
                ];
            }),
        ];
        
    }
}
