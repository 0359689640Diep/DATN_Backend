<?php

namespace App\Http\Controllers\Custommer;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Custommer\PaymentController;
use App\Http\Resources\BookingResource;
use App\Models\BookingModel;
use App\Models\PaymentsModel;
use App\Models\RommsType;
use App\Models\RoomModel;
use App\Models\ServiceBookingModel;
use App\Models\token;
use App\Models\users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;


class BookingController extends Controller
{
    private $messages;
    private $dateNow;
    protected $vnpayPayment;

    public function __construct(PaymentController $vnpayPayment)
    {
        $this->vnpayPayment = $vnpayPayment;
        $this->dateNow = date('Y-m-d H:i:s');
        $this->messages = [
            'name.required' => 'Tên không được để trống.',
            'email.required' => 'Email không được để trống.',
            'phone.required' => 'Số điện thoại không được để trống.',
            'phone.number' => 'Số điện thoại phải là số.',
            'phone.max' => 'Số điện thoại tối đa 14 số.',
            'phone.min' => 'Số điện thoại tối thiểu 10 số.',
            'email.email' => 'Email không hợp lệ.',
            'room_type_id.required' => 'Loại phòng không được để trống.',
            'check_in_date.required' => 'Thời gian nhận phòng không được để trống.',
            'check_out_date.required' => 'Thời gian trả phòng không được để trống.',
            'check_in_date.date' => 'Thời gian nhận phòng phải là ngày hợp lệ.',
            'check_out_date.date' => 'Thời gian trả phòng phải là ngày hợp lệ.',
            'check_out_date.after' => 'Thời gian trả phòng phải sau thời gian nhận phòng.',
            'total_price.required' => 'Tổng tiền không được để trống.',
            'total_price.integer' => 'Tổng tiền phải là dạng số.',
            'actual_number_people.required' => 'Số lượng người thực tế không được để trống.',
            'service.array' => 'Dịch vụ phải là mảng.',
            'service.*.exists' => 'Dịch vụ không tồn tại.',
            'quanlity_service.required' => 'Số lượng dịch vụ không được để trống.',
            'quanlity_service.number' => 'Số lượng dịch vụ phải là số.',
            'quanlity_service.min' => 'Số dịch vụ tối thiểu 1.',

            "vnp_Amount"  => 'vnp_Amount được để trống.',
            "vnp_BankCode"  => 'vnp_BankCode được để trống.',
            "vnp_BankTranNo"  => 'vnp_BankTranNo được để trống.',
            "vnp_CardType"  => 'vnp_CardType được để trống.',
            "vnp_OrderInfo"  => 'vnp_OrderInfo được để trống.',
            "vnp_PayDate"  => 'vnp_PayDate được để trống.',
            "vnp_ResponseCode"  => 'vnp_ResponseCode được để trống.',
            "vnp_TmnCode"  => 'vnp_TmnCode được để trống.',
            "vnp_TransactionNo"  => 'vnp_TransactionNo được để trống.',
            "vnp_TransactionStatus"  => 'vnp_TransactionStatus được để trống.',
            "vnp_TxnRef"  => 'vnp_TxnRef được để trống.',
            "vnp_SecureHash"  => 'vnp_SecureHash được để trống.',
        ];
    }


    public function addBooking(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "room_type_id" => "required",
            "name" => "required|string|max:255",
            "phone" => "required|string|max:14|min:10",
            "email" => "required|string|email",
            "check_in_date" => "required|date",
            "check_out_date" => "required|date|after:check_in_date",
            "total_price" => "required|integer",
            "actual_number_people" => "required",
            // Kiểm tra `service` là một mảng các object
            "service" => "nullable|array",
            "service.*.id" => "required|exists:service,id",  // Mỗi object phải có `id` tồn tại trong bảng `service`
            "service.*.quanlity_service" => "required|integer|min:1", // Số lượng dịch vụ phải là số nguyên và ít nhất là 1
            "service.*.total_price" => "required|integer|min:0", // Tổng giá phải là số nguyên và >= 0
        ], $this->messages);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Kiểm tra check_in_date không được nhỏ hơn ngày hiện tại 1 ngày
        $checkInDate = Carbon::parse(request("check_in_date"));
        $checkOutDate = Carbon::parse(request("check_out_date"));
        $currentDate = Carbon::now();

        if ($checkInDate->lt($currentDate->subDay())) {
            return response()->json(["message" => "Ngày check-in không được nhỏ hơn ngày hiện tại một ngày"], 400);
        }

        if ($checkOutDate->lte($checkInDate)) {
            return response()->json(["message" => "Ngày check-out không được bằng hoặc nhỏ hơn ngày check-in"], 400);
        }

        $roomTypeId = request("room_type_id");
        $totalPrice = request("total_price");
        $actualNumberPeople = request("actual_number_people");
        $name = request("name");
        $email = request("email");
        $phone = request("phone");
        $address = request("address");
        $note = request("note");
        $service = request("service");
        $type = request("type"); // Phân biệt có cọc hay không

        // Cập nhật thông tin người dùng
        $dataUsers = users::where("email", "=", $email)->first();
        if (!$dataUsers) {
            return response()->json(["message" => "Email chưa được sử dụng vui lòng đăng nhập để sử dụng dịch vụ"], 400);
        }

        $dataRoomsType = RommsType::where('id', '=', $roomTypeId)->first();
        if (!$dataRoomsType) {
            return response()->json(["message" => "Không tìm thấy loại phòng bạn cần đặt"], 400);
        }
        $dataUsers->update(["name" => $name, "phone" => $phone, "address" => $address]);

        // Lưu thông tin booking
        $dataBooking = [
            "user_id" => $dataUsers->id,
            "room_type_id" => $roomTypeId,
            "check_in_date" => $checkInDate,
            "check_out_date" => $checkOutDate,
            "actual_number_people" => $actualNumberPeople,
            "surcharge" => 0,
            "VAT" => 0,
            "person_in_charge" => 0,
            "note" => $note,
            "type" => 1,
            "created_at" => $this->dateNow,
        ];

        if ($type === 1) { // Cọc tiền
            $dataBooking["deposit_amount"] = $totalPrice;
            $dataBooking["deposit_status"] = "pending";
            $dataBooking["status_id"] = 12;
            $dataBooking["total_price"] = $dataRoomsType->price_per_night;
        } else { // Thanh toán toàn bộ
            $dataBooking["total_price"] = $totalPrice;
            $dataBooking["status_id"] = 11;
        }

        // Tạo bản ghi booking
        $booking = BookingModel::create($dataBooking);
        $idBooking = $booking->id;

        // Xử lý service (nếu có)
        if ($service) {
            foreach ($service as $item) {
                ServiceBookingModel::create([
                    "booking_id" => $idBooking,
                    "service_id" => $item["id"],
                    "status_id" => 1,
                    "created_at" => $this->dateNow,
                    "quanlity_service" => $item["quanlity_service"],
                    "total_price" => $item["total_price"],
                ]);
            }
        }

        // Lưu trạng thái thanh toán
        PaymentsModel::insert([
            "booking_id" => $idBooking,
            "status_id" => 12,
            "amount" => $totalPrice,
            "payment_date" => $this->dateNow,
            "payment_method" => 2,
            "created_at" => $this->dateNow,
        ]);

        // Lấy IP của người dùng
        $ipAddr = $request->ip();
        $order = [
            "code" => $idBooking,
            "info" => "order_payment_$idBooking",
            "type" => "billpayment",
            "bankCode" => "NCB",
            "total" => $totalPrice,
        ];

        // Tạo URL thanh toán
        $paymentUrl = $this->vnpayPayment->generatePaymentUrl($order, $ipAddr);
        // Trả về URL thanh toán
        return response()->json(['data' => $paymentUrl]);
    }

    public function getBookings(Request $request)
    {
        $token = $request->bearerToken();
        $dataToken = token::where("value", $token)->first();
        // Lấy ID của người dùng
        $userId = $dataToken->user_id;
        $data = BookingModel::where("user_id", "=", $userId)
            ->select("id", "room_id", "room_type_id", "status_id", "person_in_charge", "check_in_date", "check_out_date", "total_price", "created_at")
            ->with(["roomType:id,type", "status:name,color,id", "room:id,number", ])
            ->get();
            return response()->json(['data' => BookingResource::collection($data)]);
    }

    public function confirmBookings(Request $request, $id)
    {
        // Xác thực dữ liệu từ request
        $validator = Validator::make($request->all(), [
            "vnp_Amount" => "required",
            "vnp_BankCode" => "required",
            "vnp_BankTranNo" => "required",
            "vnp_CardType" => "required",
            "vnp_OrderInfo" => "required",
            "vnp_PayDate" => "required",
            "vnp_ResponseCode" => "required",
            "vnp_TmnCode" => "required",
            "vnp_TransactionNo" => "required",
            "vnp_TransactionStatus" => "required",
            "vnp_TxnRef" => "required",
            "vnp_SecureHash" => "required",
        ], $this->messages);
    
        // Nếu xác thực thất bại, trả về lỗi
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
    
        // Lấy các dữ liệu đã được xác thực
        $validatedData = $validator->validated();
    
        // Lấy các trường cụ thể
        $vnpAmount = $validatedData['vnp_Amount'];
        $vnpBankCode = $request->input('vnp_BankCode');
        $vnpPayDate = $validatedData['vnp_PayDate'];
    
        // Loại bỏ 'vnp_Amount' và 'code' khỏi dữ liệu đã xác thực để lưu vào JSON
        unset($validatedData['vnp_Amount']);
        unset($validatedData['vnp_BankCode']);
    
        // Chuyển các trường còn lại thành JSON để lưu vào 'payment_gateway_response'
        $paymentGatewayResponse = json_encode($validatedData);
    
        // Tìm booking theo ID
        $booking = BookingModel::where("id", $id)->first();
        if (!$booking) {
            return response()->json(["message" => "Không tìm thấy đơn hàng"], 404);
        }
    
        // Xác định trạng thái của đơn hàng
        $status = $booking->status_id === 12 ? 10 : 9;
    
        // Cập nhật trạng thái đơn hàng
        $booking->update([
            "status_id" => $status,
            "updated_at" => $this->dateNow,
        ]);
    
        // Cập nhật thông tin thanh toán với trạng thái, số tiền, ngày thanh toán, mã giao dịch và phản hồi từ cổng thanh toán
        PaymentsModel::where("booking_id", "=", $id)->update([
            "status_id" => $status,
            "amount" => $vnpAmount,
            "payment_date" => $vnpPayDate,
            "code" => $vnpBankCode,
            "updated_at" => $this->dateNow,
            "payment_gateway_response" => $paymentGatewayResponse,
        ]);
    
        return response()->json(["message" => "Thanh toán thành công"], 200);
    }
    
}
