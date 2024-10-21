<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingDetailResource;
use App\Http\Resources\BookingResource;
use App\Http\Resources\BookingsAdmin;
use App\Models\BookingModel;
use App\Models\ServiceBookingModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    private $dateNow;
    private $messages;

    public function __construct()
    {
        $this->dateNow = date('Y-m-d H:i:s');
        $this->messages = [
            'room_id.required' => 'Phòng không được để trống.',
            'room_id.integer' => 'Phòng phải là dạng số.',
            'room_type_id.required' => 'Loại phòng không được để trống.',
            'room_type_id.integer' => 'Loại phòng phải là dạng số.',
            'actual_number_people.required' => 'Số lượng người thực tế không được để trống.',
            'actual_number_people.integer' => 'Số lượng người thực tế  phải là dạng số.',
            'total_price.required' => 'Tổng tiền không được để trống.',
        ];
    }
    public function index(Request $request)
    {
        $query = BookingModel::select("*")
            ->with([
                "roomType:id,type,price_per_night",
                "status:name,color,id",
                "room:id,number",
                "payments:id,booking_id,status_id,code,payment_date,amount,payment_method",
                "payments.status:id,name,color",
                "serviceBooking:id,booking_id,status_id,service_id,quanlity_service,total_price,created_at",
                "serviceBooking.service:id,name",
                "serviceBooking.status:id,name,color",
            ]);
        if ($request->has("id")) {
            $query->where("id", "=", $request->input("id"));
        }
        $data = $query->get();
        return response()->json(['data' => BookingsAdmin::collection($data)]);
    }

    public function getDetailBookings(Request $request, $id)
    {
        $data = BookingModel::where("id", "=", $id)
            ->select("id", "room_id", "room_type_id", "status_id", "person_in_charge", "total_price", "created_at", "deposit_amount", "surcharge")
            ->with([
                "roomType:id,type,price_per_night",
                "status:name,color,id",
                "room:id,number",
                "payments:id,booking_id,status_id,code,payment_date,amount,payment_method",
                "payments.status:id,name,color",
                "serviceBooking:id,booking_id,status_id,service_id,quanlity_service,total_price,created_at",
                "serviceBooking.service:id,name",
                "serviceBooking.status:id,name,color",
            ])
            ->first();

        return response()->json(['data' => new BookingDetailResource($data)]);
    }

    public function checkInBookings(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            "room_id" => "required|integer",
            "room_type_id" => "required|integer",
            "actual_number_people" => "required|integer",
            "total_price" => "required",
        ], $this->messages);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $dataBooking = BookingModel::where('id', $id)->first();
        if (!$dataBooking) {
            return response()->json(['message' => 'Không tìm thấy đơn hàng'], 404);
        }
        $total_price = remove_format($request->total_price);
        $dataCheckIn = [
            "room_id" => $request->room_id,
            "room_type_id" => $request->room_type_id,
            "total_price" => $total_price,
            "actual_number_people" => $request->actual_number_people,
            "note" => $request->note,
            "updated_at" => $this->dateNow,
        ];
        $dataBooking->update($dataCheckIn);
        $service = request("service");
        // Xử lý service (nếu có)
        if ($service) {
            foreach ($service as $item) {
                // Lấy danh sách các service_id trong mảng $service
                $serviceId = $item["id"];
                // Xóa các dịch vụ không có trong mảng $service
                ServiceBookingModel::where('booking_id', $id)->delete();
            }
            foreach ($service as $item) {
                // Lấy danh sách các service_id trong mảng $service
                $serviceId = $item["id"];
                $total_price = remove_format($item["total_price"]);
                ServiceBookingModel::create([
                    "service_id" => $serviceId,
                    "booking_id" => $id,
                    "created_at" => $this->dateNow,
                    "status_id" => 1,
                    "quanlity_service" => $item["quanlity_service"],
                    "total_price" => $total_price,
                ]);
            }
        }
        return response()->json(['message' => 'Đã check-in đơn hàng thành công'], 200);
    }
}
