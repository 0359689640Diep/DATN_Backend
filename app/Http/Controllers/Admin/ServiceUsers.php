<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceUsersModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceUsers extends Controller
{
    private $messages;
    public function __construct()
    {
        $this->messages = [
            "service_id.required" => "Dịch vụ không được để trống",
            "user_id.required" => "Nhân viên phụ trách không được để trống",
            "status_id.required" => "Trạng thái không được để trống",
        ];
    }

    public function index(Request $request)
    {
        $query = ServiceUsersModel::select("id", "service_id", "user_id", "status_id",)->with(["service:id,name", "status:color,name,id", "user:id,name"]);
        $name = $request->input("name");
        if ($name) {
            $query->where('name', $name);
        }
        $data = $query->get();
        if ($data->isEmpty()) {
            return response()->json(['message' => 'Không có dữ liệu'], 404);
        }
        return response()->json(compact("data"));
    }
    public function getId($id)
    {
        $data = ServiceUsersModel::select("id", "service_id", "user_id", "status_id",)->with(["service:id,name", "status:color,name,id", "user:id,name"])->where("id", $id)->first();
        if ($data->isEmpty()) {
            return response()->json(['message' => 'Không có dữ liệu'], 404);
        }
        return response()->json(compact("data"));
    }
    public function getIdService(Request $request, $id)
    {
        $query = ServiceUsersModel::select("id", "service_id", "user_id", "status_id")
            ->with(["service:id,name", "status:color,name,id", "user:id,name"])
            ->where("service_id", $id);
        
        $name = $request->input("name");
        if ($name) {
            // Thêm điều kiện tìm kiếm theo tên của users
            $query->whereHas('user', function($q) use ($name) {
                $q->where('name', 'like', "%$name%");
            });
        }
    
        $data = $query->get();
        
        if ($data->isEmpty()) {
            return response()->json(['message' => 'Không có dữ liệu'], 404);
        }
        
        return response()->json(compact("data"));
    }
    

    public function add(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            "service_id" => "required",
            "user_id" => "required",
            "status_id" => "required",
        ], $this->messages);
        // kiểm tra validate
        $userId = $data['user_id'];
        $serviceId = $data['service_id'];

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 400);
        $serviceUserModel = ServiceUsersModel::where("user_id", $userId)->where("service_id", $serviceId)->first();
        if ($serviceUserModel) {
            return response()->json(["message" => "Nhân viên đã tồn tại trong dịch vụ"], 400);
        }
        ServiceUsersModel::create($data);
        return response()->json(["message" => "Thêm nhân viên vào dịch vụ thành công"], 200);
    }
    public function edit($id, Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            "service_id" => "required",
            "user_id" => "required",
            "status_id" => "required",
        ], $this->messages);
        $userId = $data['user_id'];
        $serviceId = $data['service_id'];

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 400);
        $serviceUserModel = ServiceUsersModel::where("user_id", $userId)->where("service_id", $serviceId)->where("id", "!=",$id)->first();
        if ($serviceUserModel) {
            return response()->json(["message" => "Nhân viên đã tồn tại trong dịch vụ"], 400);
        }
        ServiceUsersModel::where("id", $id)->update($data);
        return response()->json(["message" => "Sửa nhân viên thành công"], 200);
    }
    public function delete($id)
    {
        ServiceUsersModel::where("id", $id)->update(["status_id" => 2]);
        return response()->json(["message" => "Xóa nhân viên ra khỏi dịch vụ thành công"], 200);
    }
}
