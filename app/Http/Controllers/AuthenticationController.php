<?php

namespace App\Http\Controllers;

use App\Mail\SendMail;
use App\Mail\ForgotPassword;
use App\Models\email;
use App\Models\token;
use App\Models\User;
use App\Models\users;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthenticationController extends Controller
{
    private $messages;
    
    public function __construct(){
        $this->messages = [
            'name.required' => 'Tên không được để trống.',
            'email.required' => 'Email không được để trống.',
            'email.email' => 'Email không hợp lệ.',
            'password.required' => 'Mật khẩu không được để trống.',
            'password.min' => 'Mật khẩu phải có ít nhất :min ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.'
        ];
    }
    
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "email" => "required|string|email",
            "password" => "required|string|min:8|confirmed",
        ], $this->messages);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $password = request("password");
        $name = request("name");
        $email = request("email");
        $dataUsers = users::where("email", $email)->where("status_id", "!=", 5)->first();
        if ($dataUsers) {
            return response()->json([
                "message" => "Email đã được sử dụng.",
            ], 422);
        }
        $code = Str::random(6); // Tạo mã xác nhận

        $body = email::where("id", 1)->first();

        Mail::to($email)->send(new SendMail($name, $code,  $body->content));
    
        $user = new users();
        $user->name = $name;
        $user->email = $email;
        $user->code = $code;
        $user->role = 4;
        $user->status_id = 5;
        $user->password = bcrypt($password);
        $user->save();
        return response()->json([
            "message" => "Vui lòng nhập mã đã được gửi tới gmail của bạn để hoàn thành bước đăng ký",
        ], 201);
    }

    public function registerVerification(Request $request)
    {
        $codeRequest = $request->input("code");
        $dataUsers = users::where("code", $codeRequest)->where("status_id", 5)->first();
        
        if (!$dataUsers) {
            return response()->json(["message" => "Mã xác nhận không đúng"], 400);
        }
    
        $time = $dataUsers->created_at;
        $timeNow = Carbon::now();
        
        if ($timeNow->diffInMinutes($time) > 5) {
            return response()->json(['message' => 'Thời gian xác nhận đã quá 5 phút, vui lòng thử lại.'], 422);
        }
        
        $dataUsers->update(["status_id" => 1]);
    
        return response()->json(["message" => "Đăng ký thành công. Vui lòng đăng nhập để sử dụng dịch vụ"], 200);
    }

    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            "email" => "required|string|email",
            "password" => "required|string",
        ], $this->messages);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $email = request("email");
        $password = request("password");
        $user = users::where("email", $email)->where("status_id", "!=", 5)->first();
        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json([
                "message" => "Email hoặc mật khẩu không đúng.",
            ], 401);
        }
        if($user->status_id == 2){
            return response()->json([
                "message" => "Tài khoản đã bị vô hiệu hóa.",
            ], 401);
        }
        $token = $user->createToken('authToken')->plainTextToken;
        $timeOneHourAgo = Carbon::now()->addHour()->format('Y-m-d H:i:s');
        token::create([
            "user_id" => $user->id,
            "value" => $token,
            "expires_at" => $timeOneHourAgo,
        ]);
        return response()->json([
            "message" => "Đăng nhập thành công",
            "access_token" => $token,
            "role" => $user->role,
        ]);
    }

    public function logout(Request $request){
        $token = $request->bearerToken();
        $timeNow = now(); // Thời gian hiện tại
        token::where("value", $token)->update([
            "expires_at" => $timeNow,
            "updated_at" => $timeNow,
        ]);
        return response()->json([
            "message" => "Đăng xuất thành công",
        ]);
    }

    public function forgotPassword(Request $request){
        $validator = Validator::make($request->all(), [
            "email" => "required|string|email",
        ], $this->messages);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $email = request("email");
        $user = users::where("email", $email)->first();
        if (!$user) {
            return response()->json([
                "message" => "Email không tồn tại.",
            ], 404);
        }
        $code = Str::random(6); // Tạo mã xác nhận

        $body = email::where("id", 1)->first();
        Mail::to($email)->send(new ForgotPassword($body->content, $code));
        $time = Carbon::now()->format('Y-m-d H:i:s');
        $user->update([
            "code" => $code,
            "updated_at" => $time,
            "status_id" => 6
        ]);
        return response()->json([
            "message" => "Vui lòng nhập mã đã được gửi tới gmail của bạn để hoàn thành bước cập nhật mật khẩu !",
        ], 201);
    }
    
    public function forgotPasswordVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "code" => "required|string|max:255",
            "password" => "required|string|min:8|confirmed",
        ], $this->messages);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $password = request("password");
        $codeRequest = request("code");

        $user = users::where("code", $codeRequest)->where("status_id", 6)->first();
        if (!$user) {
            return response()->json([
                "message" => "Mã xác nhận không đúng",
            ], 400);
        }

        $time = $user->updated_at;
        $timeNow = Carbon::now();

        if ($timeNow->diffInMinutes($time) > 5) {
            return response()->json([
                'message' => 'Thời gian xác nhận đã quá 5 phút, vui lòng thử lại.',
            ], 422);
        }

        // Cập nhật mật khẩu vào database ở đây
        $user->update([
            "password" => bcrypt($password),
            "status_id" => 1,
            "updated_at" => Carbon::now(),
        ]);
        $request->session()->forget('forgotPassword');
        return response()->json([
            "message" => "Cập nhật mật khẩu mới thành công. Vui lòng đăng nhập để sử dụng dịch vụ",
        ], 200);
    }
}
