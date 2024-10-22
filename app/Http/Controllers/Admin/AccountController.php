<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{
    private $messages;

    public function __construct()
    {
        $this->messages = [
            'name.required' => 'Tên không được để trống.',
            'role.required' => 'Quyền không được để trống.',
            'email.required' => 'Email không được để trống.',
            'email.email' => 'Email không hợp lệ.',
            'password.required' => 'Mật khẩu không được để trống.',
            'password.min' => 'Mật khẩu phải có ít nhất :min ký tự.',
            'image.mimes' => 'Vui lòng chọn file có đuôi jpeg,jpg,png.',
        ];
    }

    public function index(Request $request){
        $query = users::select("id", "name", "email", "image", "role", "status_id")->with("status:name,color,id");
        $email = $request->input("email");
        $statusId = $request->input("status_id");
        if($email){
            $query->where('email', $email);
        }
        if($statusId){
            $query->where('status_id', $statusId);
        }
        $data = $query->get();
        if ($data->isEmpty()) {
            return response()->json(['message' => 'Không có dữ liệu'], 404);
        }
        $data = $data->map(function ($item) {
            return [
                "id" => $item->id,
                "name" => $item->name,
                "email" => $item->email,
                "image" => asset('storage/' .$item->image),
                "role" => $item->role,
                "status_id" => $item->status_id,
                "status_name" => $item->status->name,
                "status_color" => $item->status->color,
            ];
        });
        return response()->json(compact("data"));
    }
    public function getId($id){
        $data = users::select("name", "email", "image", "role", "status_id")->where("id", $id)->first();
        if ($data->isEmpty()) {
            return response()->json(['message' => 'Không có dữ liệu'], 404);
        }
        return response()->json($data);
    }


    public function add(Request $request){
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "email" => "required|string|email",
            "password" => "required|string|min:8",
            "role" => "required",
            'image' => 'image|mimes:jpeg,jpg,png'
        ], $this->messages);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $password = request("password");
        $name = request("name");
        $email = request("email");
        $role = request("role");
        $statusId = request("status_id");

        $image = "";
        $dataUsers = users::where("email", $email)->first();
        if ($dataUsers) {
            return response()->json([
                "message" => "Email đã được sử dụng.",
            ], 422);
        }
        // duyệt qua từng file và lưu trữ
        if ($request->hasFile('images')) {
            $files = $request->file('images');
            // Validate each file
            if (!$files->isValid()) {
                return response()->json(["message" => "File không hợp lệ vui lòng thử lại"], 400);
            }

            // Store the file with a unique name
            $image = $files->store('uploads', 'public');
        }

        users::create([
            "name" => $name,
            "email" => $email,
            "password" => bcrypt($password),
            "image" => $image,
            "role" => $role,
            "status_id" => $statusId
        ]);
        return response()->json([
            "message" => "Thêm tài khoản thành công.",
        ], 200);
        
    }
    public function edit($id, Request $request){
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "email" => "required|string|email",
            "password" => "nullable|string|min:8",
            "role" => "required",
            'image' => 'image|mimes:jpeg,jpg,png'
        ], $this->messages);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $password = request("password");
        $name = request("name");
        $email = request("email");
        $role = request("role");
        $image = "";
        $statusId = request("status_id");

        $dataUsers = users::where("email", $email)->where("id", "!=", $id)->first();
        $dataUsersOld = users::where("id", "=", $id)->first();
        if ($dataUsers) {
            return response()->json([
                "message" => "Email đã được sử dụng.",
            ], 422);
        }
        // duyệt qua từng file và lưu trữ
        if ($request->hasFile('images')) {
            $files = $request->file('images');
            // Validate each file
            if (!$files->isValid()) {
                return response()->json(["message" => "File không hợp lệ vui lòng thử lại"], 400);
            }

            if (Storage::disk('public')->exists($dataUsersOld->image)) {
                Storage::disk("public")->delete($dataUsersOld->image);
            }

            // Store the file with a unique name
            $image = $files->store('uploads', 'public');
        }else{
            $image = $dataUsersOld->image;
        }

        $dataUpdate =[
            "name" => $name,
            "email" => $email,
            "image" => $image,
            "role" => $role,
            "status_id" => $statusId
        ];

        if(!empty($password)){
            $dataUpdate["password"] =  bcrypt($password);
        }

        users::where("id", $id)->update($dataUpdate);
        return response()->json([
            "message" => "Cập nhật tài khoản thành công.",
        ], 200);
        
    }

    public function delete($id){
        users::where("id", $id)->update(["status_id"=> 2]);
        return response()->json([
            "message" => "Khóa tài khoản thành công.",
        ], 200);
    }
}