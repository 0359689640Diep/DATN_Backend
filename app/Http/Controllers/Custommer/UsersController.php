<?php

namespace App\Http\Controllers\Custommer;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\token;
use App\Models\users;
use Illuminate\Http\Request;

class UsersController extends Controller
{
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
}
