<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingsAdmin extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'room_type_id' => $this->room_type_id,
            'person_in_charge' => $this->person_in_charge,
            'check_in_date' => Carbon::parse($this->check_in_date)->format('d-m-Y'), // Định dạng ngày dd-mm-yy
            'check_out_date' => Carbon::parse($this->check_out_date)->format('d-m-Y'), // Định dạng ngày dd-mm-yy
            'total_price' => number_format($this->total_price ?? 0), // Định dạng tiền tệ Việt Nam
            'created_at' => Carbon::parse($this->created_at)->format('d-m-Y'), // Định dạng ngày dd-mm-yy
            'actual_number_people' => $this->actual_number_people,
            'surcharge' => number_format($this->surcharge),
            'type' => $this->type,
            'note' => $this->note,
            'deposit_amount' => number_format($this->deposit_amount ?? 0), // Định dạng tiền tệ Việt Nam
            'deposit_amount' => $this->deposit_amount,
            'deposit_date' => Carbon::parse($this->deposit_date)->format('d-m-Y'), // Định dạng ngày dd-mm-yy
            'deposit_refund_date' => Carbon::parse($this->deposit_refund_date)->format('d-m-Y'), // Định dạng ngày dd-mm-yy
            'room' => [
                'number' => $this->room?->number,
            ],
            'room_type' => [
                'type' => $this->roomType->type,
            ],
            'status' => [
                'name' => $this->status->name,
                'color' => $this->status->color,
            ],
            // Xử lý nhiều payments
            'payments' => $this->payments->map(function ($payment) {
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
            'service_booking' => $this->serviceBooking->map(function ($serviceBooking) {
                return [
                    'created_at' => Carbon::parse($serviceBooking->created_at)->format('d-m-Y'),
                    'quanlity_service' => $serviceBooking->quanlity_service,
                    'total_price' => number_format($serviceBooking->total_price),
                    'service_name' => $serviceBooking->service->name ?? null, // Kiểm tra service có tồn tại không
                    'status_color' => $serviceBooking->status->color ?? null,
                    'status_name' => $serviceBooking->status->name ?? null,
                    'id' => $serviceBooking->id ?? null,
                ];
            }),
        ];
    }
}
