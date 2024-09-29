<?php

namespace App\Http\Controllers;

use App\Mail\SendMail;
use App\Mail\ForgotPassword;
use App\Models\email;
use App\Models\token;
use App\Models\users;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

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
        $dataUsers = users::where("email", $email)->first();
        if ($dataUsers) {
            return response()->json([
                "message" => "Email đã được sử dụng.",
            ], 422);
        }
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $code = '';
        for ($i = 0; $i < 6; $i++) {
            $code .= $characters[rand(0, $charactersLength - 1)];
        }

        $body = email::where("id", 1)->first();
        Mail::to($email)->send(new SendMail($name, $code,  $body->content));
        $time = Carbon::now()->format('Y-m-d H:i:s');
        $request->session()->put('register', [
            "name" => $name,
            "email" => $email,
            "password" => $password,
            "code" => $code,
            "time" => $time,
        ]);
        return response()->json([
            "message" => "Vui lòng nhập mã đã được gửi tới gmail của bạn để hoàn thành bước đăng ký",
        ], 201);
    }

    public function registerVerification(Request $request)
    {
        $registerData = $request->session()->get('register');

        // Truy cập từng phần tử trong session
        $name = $registerData['name'];
        $email = $registerData['email'];
        $password = $registerData['password'];
        $code = $registerData['code'];
        $codeRequest = request("code");
        $time = $registerData['time'];
        $timeNow = Carbon::now();
        
        if($codeRequest !== $code){
            return response()->json([
                "message" => "Mã xác nhận không đúng",
            ], 400);
        }
        if ($timeNow->diffInMinutes($time) > 5) {
            return response()->json([
                'message' => 'Thời gian xác nhận đã quá 5 phút, vui lòng thử lại.',
            ], 422);
        }
        $user = new users();
        $user->name = $name;
        $user->email = $email;
        $user->role = 4;
        $user->password = bcrypt($password);
        $user->save();
        $request->session()->forget('register');
        return response()->json([
            "message" => "Đăng ký thành công. Vui lòng đăng nhập để sử dụng dịch vụ",
        ], 200);
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
        $user = users::where("email", $email)->first();
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
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $code = '';
        for ($i = 0; $i < 6; $i++) {
            $code.= $characters[rand(0, $charactersLength - 1)];
        }
        $body = email::where("id", 1)->first();
        Mail::to($email)->send(new ForgotPassword($body->content, $code));
        $time = Carbon::now()->format('Y-m-d H:i:s');
        $request->session()->put('forgotPassword', [
            "code" => $code,
            "time" => $time,
            "id" => $user->id,
        ]);
        return response()->json([
            "message" => "Vui lòng nhập mã đã được gửi tới gmail của bạn để hoàn thành bước đăng ký",
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

        $forgotPasswordData = $request->session()->get('forgotPassword');

        // Truy cập từng phần tử trong session
        $code = $forgotPasswordData['code'];
        $time = $forgotPasswordData['time'];
        $id = $forgotPasswordData['id'];
        $timeNow = Carbon::now();
        
        if($codeRequest !== $code){
            return response()->json([
                "message" => "Mã xác nhận không đúng",
            ], 400);
        }
        if ($timeNow->diffInMinutes($time) > 5) {
            return response()->json([
                'message' => 'Thời gian xác nhận đã quá 5 phút, vui lòng thử lại.',
            ], 422);
        }

        // Cập nhật mật khẩu vào database ở đây
        users::where("id", $id)->update([
            "password" => bcrypt($password),
            "updated_at" => Carbon::now(),
        ]);
        $request->session()->forget('forgotPassword');
        return response()->json([
            "message" => "Cập nhật mật khẩu mới thành công. Vui lòng đăng nhập để sử dụng dịch vụ",
        ], 200);
    }
}
