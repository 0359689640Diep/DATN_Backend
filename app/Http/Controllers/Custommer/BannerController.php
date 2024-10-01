<?php

namespace App\Http\Controllers\Custommer;

use App\Http\Controllers\Controller;
use App\Models\BannerImage;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index()
    {
        // Lấy danh sách ảnh banner với thông tin trạng thái
        $banners = BannerImage::select("image_url")
            ->where("status_id", "=", 1)
            ->get();
            if ($banners->isEmpty()) {
                return response()->json(['message' => 'Không có dữ liệu'], 404);
            }
        // Chuyển đổi dữ liệu thành mảng với URL của ảnh
        $data = $banners->map(function ($banner) {
            return [
                'image_url' => asset('storage/' . $banner->image_url), // Sử dụng 'storage/' nếu lưu trong thư mục storage
            ];
        });

        return response()->json(compact("data"));

    }
}
