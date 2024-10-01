<?php

namespace App\Http\Controllers\Admin\Banner;

use App\Http\Controllers\Controller;
use App\Models\BannerImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BannerController extends Controller
{
    private $messages;

    public function __construct()
    {
        $this->messages = [
            'image.required' => 'Ảnh cho các loại phòng không được để trống.',
            'image.mimes' => 'Vui lòng chọn file có đuôi jpeg,jpg,png.',
        ];
    }

    public function index()
    {
        // Lấy danh sách ảnh banner với thông tin trạng thái
        $banners = BannerImage::select("status_id", "id", "image_url")
            ->with("status:name,color,id")
            ->get();
            if ($banners->isEmpty()) {
                return response()->json(['message' => 'Không có dữ liệu'], 404);
            }
        // Chuyển đổi dữ liệu thành mảng với URL của ảnh
        $bannersWithUrls = $banners->map(function ($banner) {
            return [
                'id' => $banner->id,
                'status_id' => $banner->status_id, // Sửa tên thuộc tính
                'name' => $banner->status->name ?? null, // Truy cập thông tin trạng thái
                'color' => $banner->status->color ?? null, // Truy cập thông tin trạng thái
                'image_url' => asset('storage/' . $banner->image_url), // Sử dụng 'storage/' nếu lưu trong thư mục storage
            ];
        });

        return response()->json($bannersWithUrls);

    }


    public function getId($id)
    {
        // lấy danh sách ảnh banner
        $banners = BannerImage::where('id', $id)->get();
        if ($banners->isEmpty()) {
            return response()->json(['message' => 'Không có dữ liệu'], 404);
        }
        return response()->json($banners);
    }

    public function add(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'images.*' => 'image|mimes:jpeg,jpg,png'
        ], $this->messages);
        // kiem tra validate
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 400);

        // duyệt qua từng file và lưu trữ
        if ($request->hasFile('images')) {
            $files = $request->file('images');
            foreach ($files as $file) {
                // Validate each file
                if (!$file->isValid()) {
                    return response()->json(["message" => "File không hợp lệ vui lòng thử lại"], 400);
                }

                // Store the file with a unique name
                $path = $file->store('uploads', 'public');

                // Save the file path to the database
                // Add the image to the database
                BannerImage::create([
                    "stats_id" => 1,
                    "image_url" => $path
                ]);
            }
        }

        return response()->json(["message" => "Thêm banner thành công"]);
    }

    public function edit(Request $request, $id)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'images' => 'image|mimes:jpeg,jpg,png',
        ], $this->messages);
        // kiem tra validate
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 400);

        // duyệt qua từng file và lưu trữ
        if ($request->hasFile('images')) {
            $files = $request->file('images');
            // Validate each file
            if (!$files->isValid()) {
                return response()->json(["message" => "File không hợp lệ vui lòng thử lại"], 400);
            }

            $oldImage = BannerImage::where('id', $id)->first();
            if (!empty($oldImage)) {
                if (Storage::disk('public')->exists($oldImage->image_url)) {
                    Storage::disk("public")->delete($oldImage->image_url);
                }
                // Store the file with a unique name
                $path = $files->store('uploads', 'public');

                // Save the file path to the database
                // Add the image to the database
                $oldImage->update([
                    "image_url" => $path
                ]);
                return response()->json(["message" => "Cập banner thành công"]);
            }
            return response()->json(["errors" => "Không tìm thấy ảnh"], 404);
        }
    }

    public function delete($id)
    {
        BannerImage::where('id', $id)->update(["stats_id" => 2]);
        return response()->json(["message" => "Ẩn banner thành công"]);
    }
}