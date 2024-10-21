<?php

namespace App\Http\Controllers\Admin;

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
        $data = $banners->map(function ($banner) {
            return [
                'id' => $banner->id,
                'status_id' => $banner->status_id, // Sửa tên thuộc tính
                'name' => $banner->status->name ?? null, // Truy cập thông tin trạng thái
                'color' => $banner->status->color ?? null, // Truy cập thông tin trạng thái
                'image_url' => asset('storage/' . $banner->image_url), // Sử dụng 'storage/' nếu lưu trong thư mục storage
            ];
        });

        return response()->json(compact("data"));

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
        $statusId = request("status_id");
        $validator = Validator::make($input, [
            'images.*' => 'image|mimes:jpeg,jpg,png'
        ], $this->messages);
        // kiem tra validate
        if ($validator->fails()) return response()->json($validator->errors(), 422);

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
                    "status_id" => $statusId,
                    "image_url" => $path
                ]);
            }
        }

        return response()->json(["message" => "Thêm banner thành công"]);
    }

    public function edit(Request $request, $id)
    {
        $statusId = request("status_id");
        // duyệt qua từng file và lưu trữ
        $oldImage = BannerImage::where('id', $id)->first();
        $path = $oldImage->image_url;
        if ($request->hasFile('images')) {
            $files = $request->file('images');
            // Validate each file
            if (!$files->isValid()) {
                return response()->json(["message" => "File không hợp lệ vui lòng thử lại"], 400);
            }

            if (!empty($oldImage)) {
                if (Storage::disk('public')->exists($oldImage->image_url)) {
                    Storage::disk("public")->delete($oldImage->image_url);
                }
                // Store the file with a unique name
                $path = $files->store('uploads', 'public');
            }
        }
        $oldImage->update([
            "image_url" => $path,
            "status_id" => $statusId,
        ]);
        return response()->json(["message" => "Cập banner thành công"]);
    }

    public function delete($id)
    {
        BannerImage::where('id', $id)->update(["stats_id" => 2]);
        return response()->json(["message" => "Ẩn banner thành công"]);
    }
}