<?php

namespace App\Http\Controllers\Admin\Rooms;

use App\Http\Controllers\Controller;
use App\Models\RoomModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoomsController extends Controller
{
    private $messages;

    public function __construct()
    {
        $this->messages = [
            'room_type_id.required' => 'Loại phòng không được để trống.',
            'status_id.required' => 'Loại phòng không được để trống.',
            'number.required' => 'Số phòng không được để trống.',
            'number.integer' => 'Số phòng phải là dạng số.',
            'price_per_night.required' => 'Giá cho một đêm không được để trống.',
            'price_per_night.integer' => 'Giá cho một đêm phải là dạng số.',
            'defaul_people.required' => 'Số lượng người mặc định không được để trống.',
            'defaul_people.integer' => 'Số lượng người mặc định phải là dạng số.',
        ];
    }

    public function index(Request $request)
    {
        // Lấy các tham số lọc từ request (nếu có)
        $number = $request->input('number');
        $roomTypeId = $request->input('room_type_id');
    
        // Tạo một truy vấn cơ bản
        $query = RoomModel::select(
            "id",
            "room_type_id",
            "number",
            "price_per_night",
            "description",
            "defaul_people",
            "status_id"
        )->with(["roomType:id,type", "status:color,name,id"]);
    
        // Thêm điều kiện lọc theo 'number' nếu có
        if ($number) {
            $query->where('number', $number);
        }
    
        // Thêm điều kiện lọc theo 'room_type_id' nếu có
        if ($roomTypeId) {
            $query->where('room_type_id', $roomTypeId);
        }
    
        // Lấy dữ liệu sau khi áp dụng các điều kiện lọc
        $data = $query->get();
    
        // Kiểm tra dữ liệu có trống không
        if ($data->isEmpty()) {
            return response()->json(['error' => 'Không có dữ liệu'], 404);
        }
    
        // Trả về dữ liệu nếu có
        return response()->json(compact("data"), 200);
    }
    
    public function getById($id)
    {
        $data = RoomModel::select(
            "room_type_id",
            "number",
            "price_per_night",
            "description",
            "status_id",
            "defaul_people",
        )->with(["roomType:id,type", "status:color,name,id"])->where("id", $id)->get();
        if ($data->isEmpty()) {
            // Nếu dữ liệu trống, trả về 404 với thông báo lỗi
            return response()->json(['error' => 'Không có dữ liệu bạn cần tìm'], 404);
        }
        return response()->json(compact("data"), 200);
    }
    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_type_id' => 'required',
            'number' => 'required|integer',
            'price_per_night' => 'required|integer',
            'defaul_people' => 'required|integer',
            'status_id' => 'required',
        ], $this->messages);
        $input = $request->all();
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $roomData = RoomModel::where("number", "=", $input["number"])->first();
        if ($roomData) return response()->json(['message' => 'Phòng này đã tồn tại'], 400);
        $room = RoomModel::create($request->all());
        return response()->json(["message" => "Tạo phòng thành công"], 201);
    }
    public function edit(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'room_type_id' => 'required',
            'number' => 'required|integer',
            'price_per_night' => 'required|integer',
            'defaul_people' => 'required|integer',
            'status_id' => 'required',

        ], $this->messages);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $input = $request->all();
        $roomData = RoomModel::where("number", "=", $input["number"])->where("id", "!=", $id)->first();
        if ($roomData) return response()->json(['message' => 'Phòng này đã tồn tại'], 400);
        $room = RoomModel::where("id", $id)->update($request->all());
        return response()->json(["message" => "Cập nhật phòng thành công"], 201);
    }

    public function delete($id)
    {
        RoomModel::where("id", $id)->update(["status_id" => 2]);
        return response()->json(['message' => 'Xóa phòng thành công'], 200);
    }
}
