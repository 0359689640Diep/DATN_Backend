<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingModel;
use App\Models\BookingServiceUsersModel;
use App\Models\ServiceBookingModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookingServiceUserController extends Controller
{
    private $dateNow;
    private $messages;

    public function __construct()
    {
        $this->dateNow = date('Y-m-d H:i:s');
        $this->messages = [
            'booking_service_id.required' => 'Mã đơn hàng dịch vụ không được để trống.',
            'booking_service_id.integer' => 'Mã đơn hàng dịch vụ phải là dạng số.',
            'user_id.required' => 'Vui lòng chọn ID nhân viên cho mỗi mục.',
            'user_id.integer' => 'ID nhân viên phải là dạng số.',
        ];
    }
    
    public function addUsersToBookingsService(Request $request)
    {
        // Lấy danh sách người dùng từ request
        $listUsers = $request->input('list_users');
        
        // Kiểm tra xem list_users có tồn tại và là mảng không
        if (!is_array($listUsers)) {
            return response()->json(['error' => 'Danh sách người dùng không hợp lệ.'], 422);
        }

        foreach ($listUsers as $item) {
            // Validate từng mục trong list_users
            $validator = Validator::make($item, [
                'booking_service_id' => ['required', 'integer'],
                'user_id' => ['required', 'integer'],
            ], $this->messages);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            // Tạo mới BookingServiceUsersModel
            BookingServiceUsersModel::create([
                "booking_service_id" => $item['booking_service_id'], // Chắc chắn là 'booking_service_id' ở đây
                "user_id" => $item['user_id'],
                "created_at" => $this->dateNow,
            ]);
        }

        return response()->json(["message" => "Thêm nhân viên phụ trách thành công"], 200);
    }
}
