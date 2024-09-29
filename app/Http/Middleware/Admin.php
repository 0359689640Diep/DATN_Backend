<?php

namespace App\Http\Middleware;

use App\Models\token;
use App\Models\users;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
    
        if (!$token) {
            return response()->json(['message' => 'Vui lòng đăng nhập để sử dụng dịch vụ'], 401);
        }
    
        $dataToken = token::where("value", $token)->first();
    
        if (!$dataToken) {
            return response()->json(['message' => 'Vui lòng đăng nhập để sử dụng dịch vụ'], 401);
        }
    
        $user = users::select("role", "status_id")->where("id", $dataToken->user_id)->first();
    
        if (!$user || $user->role != 1) {
            return response()->json(['message' => 'Bạn không có quyền sử dụng dịch vụ này'], 403);
        }
        if($user->status_id == 2){
            return response()->json([
                "message" => "Tài khoản đã bị vô hiệu hóa.",
            ], 401);
        }
    
        return $next($request);
    }
    
}
