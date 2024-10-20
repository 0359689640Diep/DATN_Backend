<?php

namespace App\Http\Controllers\Admin\Service;

use App\Http\Controllers\Controller;
use App\Models\ServiceModel;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    private $messages;
    public function __construct() {
        $this->messages = [
            "room_type_id.required" => "Vui lòng chọn loại phòng",
            "name.required" => "Vui lòng nhập tên dịch vụ",
        ];
    }

    public function index(Request $request) {
        $query = ServiceModel::select("id", "name", "price", "description", "room_type_id", "status_id")->with(["roomType:id,type", "status:color,name,id"]);
        $name = $request->input("name");
        if($name){
            $query->where('name', $name);
        }
        $roomTypeId = $request->input("room_type_id");
        if($roomTypeId){
            $query->where('room_type_id', $roomTypeId);
        }
        $data = $query->get();
        if ($data->isEmpty()) {
            return response()->json(['message' => 'Không có dữ liệu'], 404);
        }
        return response()->json(compact("data"));
    }
    public function getId($id) {
        $data = ServiceModel::select("id", "name", "price", "description", "room_type_id", "status_id")->where("id", $id)->first();
        if ($data->isEmpty()) {
            return response()->json(['message' => 'Không có dữ liệu'], 404);
        }
        return response()->json($data);
    }

    public function add(Request $request) {
        $request->validate([
            "room_type_id" => "required",
            "name" => "required",
        ], $this->messages);
        $name = request("name");
        $dataService = ServiceModel::where("name", $name)->first();
        if($dataService){
            return response()->json(["message" => "Tên dịch vụ đã tồn tại"], 400);
        }
        $data = $request->all();
        ServiceModel::create($data);
        return response()->json(["message" => "Thêm dịch vụ thành công"], 201);
    }
    public function edit($id, Request $request) {
        $request->validate([
            "room_type_id" => "required",
            "name" => "required",
        ], $this->messages);
        $name = request("name");
        $dataService = ServiceModel::where("id","!=", $id)->where("name", $name)->first();
        if($dataService){
            return response()->json(["message" => "Tên dịch vụ đã tồn tại"], 400);
        }
        $data = $request->all();
        ServiceModel::where("id", $id)->update($data);
        return response()->json(["message" => "Sửa dịch vụ thành công"], 201);
    }
    public function delete($id) {
        ServiceModel::where("id", $id)->update(["status_id" => 2]);
        return response()->json(["message" => "Xóa dịch vụ thành công"], 201);
    }
}
