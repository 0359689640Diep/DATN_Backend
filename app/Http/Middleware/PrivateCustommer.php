<?php

namespace App\Http\Middleware;

use App\Models\token;
use App\Models\users;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PrivateCustommer
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

        $timeNow = now(); // Thời gian hiện tại
        $expiresAt = $dataToken->expires_at; // Thời gian hết hạn của token
        
        // So sánh thời gian hiện tại với thời gian hết hạn của token
        // if ($timeNow->greaterThan($expiresAt)) {
        //     return response()->json(['message' => 'Phiên làm việc đã hết hạn'], 401);
        // }
    
        $user = users::select("role", "status_id")->where("id", $dataToken->user_id)->first();
        if (!$user || $user->role != 4) {
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
