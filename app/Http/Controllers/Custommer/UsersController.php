<?php

namespace App\Http\Controllers\Custommer;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\token;
use App\Models\users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UsersController extends Controller
{
    private $messages;

    public function __construct()
    {
        $this->messages = [
            'name.required' => 'Tên không được để trống.',
            'address.required' => 'Địa chỉ không được để trống.',
            'phone.required' => 'Số điện thoại không được để trống.',
            'phone.numeric' => 'Số điện thoại không hợp lệ.',
            'email.required' => 'Email không được để trống.',
            'email.email' => 'Email không hợp lệ.',
            'password_new.min' => 'Mật khẩu mới phải có ít nhất :min ký tự.',
            'password_old.min' => 'Mật khẩu cũ phải có ít nhất :min ký tự.',
            'image.mimes' => 'Vui lòng chọn file có đuôi jpeg,jpg,png.',
        ];
    }
    public function getUsers(Request $request)
    {
        $token = $request->bearerToken();
        $dataToken = token::where("value", $token)->first();
        // Lấy người dùng hiện tại đã đăng nhập thông qua token
        $user = users::select("name", "email", "image", "phone", "address", "id")->where("id", $dataToken->user_id)->first();

        // Trả về dữ liệu người dùng hoặc ID
        return response()->json([
            'data' => new UserResource($user)
        ]);
    }

    public function updateUsers(Request $request)
    {
        // Lấy token và user id
        $token = $request->bearerToken();
        $dataToken = token::where("value", $token)->first();
        $id = $dataToken->user_id;

        // Thực hiện validate dữ liệu
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "email" => "required|string|email",
            "phone" => "required|numeric",
            "address" => "required|string",
            "password_new" => "nullable|string|min:8",  // Mật khẩu mới có thể không bắt buộc
            "password_old" => "nullable|string|min:8",  // Mật khẩu cũ cũng không bắt buộc
        ], $this->messages);

        // Trả về lỗi nếu validation không đạt yêu cầu
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Lấy thông tin từ request
        $name = $request->input('name');
        $email = $request->input('email');
        $phone = $request->input('phone');
        $address = $request->input('address');
        $passwordNew = $request->input('password_new');
        $passwordOld = $request->input('password_old');

        // Kiểm tra email có đang được sử dụng bởi user khác hay không
        $dataUsers = users::where("email", $email)->where("id", "!=", $id)->first();
        $dataUsersOld = users::where("id", "=", $id)->first();
        if ($dataUsers) {
            return response()->json([
                "message" => "Email đã được sử dụng.",
            ], 422);
        }

        // Kiểm tra nếu người dùng muốn đổi mật khẩu
        if ($passwordNew) {
            if (!$passwordOld || !Hash::check($passwordOld, $dataUsersOld->password)) {
                return response()->json([
                    "message" => "Mật khẩu cũ không hợp lệ vui lòng kiểm tra lại.",
                ], 422);
            }
            // Cập nhật mật khẩu mới
            users::where("id", $id)->update(["password" => bcrypt($passwordNew)]);
        }

        // Xử lý cập nhật ảnh
        if ($request->hasFile('image')) {
            $file = $request->file('image');

            // Kiểm tra xem file có hợp lệ không
            if (!$file->isValid()) {
                return response()->json(["message" => "File không hợp lệ vui lòng thử lại"], 400);
            }

            // Xóa ảnh cũ nếu tồn tại
            if (Storage::disk('public')->exists($dataUsersOld->image)) {
                Storage::disk("public")->delete($dataUsersOld->image);
            }

            // Lưu file mới và tạo đường dẫn
            $image = $file->store('uploads', 'public');
            users::where("id", $id)->update(["image" => $image]);
        }

        // Cập nhật thông tin người dùng
        users::where("id", $id)->update([
            "name" => $name,
            "email" => $email,
            "phone" => $phone,
            "address" => $address,
        ]);

        // Trả về thông báo cập nhật thành công
        return response()->json([
            "message" => "Cập nhật tài khoản thành công.",
        ], 200);
    }
}
