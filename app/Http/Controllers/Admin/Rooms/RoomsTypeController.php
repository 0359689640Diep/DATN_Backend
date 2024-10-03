<?php

namespace App\Http\Controllers\Admin\Rooms;

use App\Http\Controllers\Controller;
use App\Models\RommsType;
use App\Models\RommsImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

use function PHPUnit\Framework\returnSelf;

class RoomsTypeController extends Controller
{
    private $messages;

    public function __construct()
    {
        $this->messages = [
            'type.required' => 'Tên loại phòng không được để trống.',
            'images.required' => 'Ảnh cho các loại phòng không được để trống.',
            'images.mimes' => 'Vui lòng chọn file có đuôi jpeg,jpg,png.',
            "images.max" => "File không được vượt quá 2048 MB",
            "image_id.array" => "image_id phải là một mảng",
            "image_id.integer" => "image_id phải là một số",
            "image_id.exists" => "image_id không tồn tại trên database",
            'price_per_night.required' => 'Giá cho một đêm không được để trống.',
            'price_per_night.integer' => 'Giá cho một đêm phải là dạng số.',
            'defaul_people.required' => 'Số lượng người mặc định không được để trống.',
            'defaul_people.integer' => 'Số lượng người mặc định phải là dạng số.',
        ];
    }

    public function addRoomType(Request $request)
    {
        $input = $request->all();
        $dataAddRoomType = [
            'type' => $input['type'],
            'price_per_night' => $input['price_per_night'],
            'defaul_people' => $input['defaul_people'],
            'description' => $input['description'],
        ];
        $validator = Validator::make($input, [
            'type' => 'required',
            "price_per_night",
            "description",
            "defaul_people",
            'images' => 'required',
            'images.*' => 'file|image|mimes:jpeg,jpg,png' 
        ], $this->messages);
        // kiểm tra validate
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 400);

        $dataRoomType = RommsType::where("type", $input["type"])->exists();

        if ($dataRoomType)  return response()->json(["message" => "Loại phòng đã tồn tại"], 400);
        // lưu trữ dữ liệu vào room type
        $roomType = RommsType::create($dataAddRoomType);
        
        $id = $roomType->id;

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
                RommsImage::create([
                    "room_type_id" => $id,
                    "image_url" => $path,
                    "description" => $file->getClientOriginalName()
                ]);
            }
        }

        return response()->json(["message" => "Thêm loại phòng thành công"], 200);
    }

    public function getRoomType(Request $request)
    {
        $type = $request->input('type');

        $query = RommsType::select("id", "type", "defaul_people", "description", "price_per_night")
            ->with("roomImages:description,image_url,room_type_id,id");
        
        if($type){
            $query = $query->where("type", $type);
        }
        $dataRoomType = $query->get();
        if ($dataRoomType->isEmpty()) {
            return response()->json(['message' => 'Không có dữ liệu'], 404);
        }
        
        $data = $dataRoomType->map(function ($roomType) {
            // Lấy tất cả các hình ảnh liên quan đến phòng từ roomImages
            $images = $roomType->roomImages->map(function ($image) {
                return [
                    "id" => $image->id,
                    "description" => $image->description ?? "", 
                    "image_url" => $image->image_url ? asset('storage/' . $image->image_url) : "",
                ];
            });
    
            return [
                "id" => $roomType->id,
                "type" => $roomType->type,
                "defaul_people" => $roomType->defaul_people,
                "description" => $roomType->description,
                "price_per_night" => $roomType->price_per_night,
                "room_images" => $images // Trả về mảng các hình ảnh
            ];
        });
    
        return response()->json(["data" => $data], 200);
    }
    
    public function getRoomTypeById($id)
    {
        $dataRoomType = RommsType::select("id", "type")->where("id", $id)->with("roomImages:description,image_url,room_type_id,id")->first();
        if ($dataRoomType->isEmpty()) {
            return response()->json(['message' => 'Không có dữ liệu'], 404);
        }
        return response()->json(["data" => $dataRoomType], 200);
    }

    public function editRoomType(Request $request, $id)
    {
        
        $input = $request->all();
        $dataAddRoomType = [
            'type' => $input['type'],
            'price_per_night' => $input['price_per_night'],
            'defaul_people' => $input['defaul_people'],
            'description' => $input['description'],
        ];

        // Kiểm tra sự tồn tại của loại phòng với tên đã được cập nhật
        $existingRoomType = RommsType::where('type', $input['type'])->where('id', '!=', $id)->exists();
        if ($existingRoomType) {
            return response()->json(['message' => 'Loại phòng đã tồn tại'], 400);
        }

        // Cập nhật loại phòng
        RommsType::where("id", $id)->update($dataAddRoomType);
        // Lấy danh sách các ảnh cũ từ cơ sở dữ liệu
        
        // Duyệt qua từng ảnh mới để xác định ảnh nào cần thay thế
        if ($request->hasFile('images')) {
            $files = $request->file('images');
            foreach ($files as $index => $file) {
                $imageId = $input['image_id'][$index] ?? null;
                $oldImages = RommsImage::where('id', $imageId)->first();

                if ($imageId && !empty($oldImages)) {
                    // Xóa ảnh cũ khỏi server
                    if (Storage::disk('public')->exists($oldImages->image_url)) {
                        Storage::disk('public')->delete($oldImages->image_url);
                    }

                    // Cập nhật đường dẫn ảnh mới trong cơ sở dữ liệu
                    $path = $file->store('uploads', 'public');
                    $oldImages->update([
                        'image_url' => $path,
                        'description' => $file->getClientOriginalName(),
                    ]);
                }
            }
        }

        return response()->json(['message' => 'Cập nhật loại phòng thành công']);
    }
}
