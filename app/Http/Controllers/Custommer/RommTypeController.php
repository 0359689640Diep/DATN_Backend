<?php

namespace App\Http\Controllers\Custommer;

use App\Http\Controllers\Controller;
use App\Models\RommsType;
use Illuminate\Http\Request;

class RommTypeController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->input('type');
        $price_per_night = $request->input('price_per_night');


        $query = RommsType::select("id", "type", "price_per_night", "description", "defaul_people")
            ->with("roomImages:description,image_url,room_type_id,id");
        
        if($type){
            $query = $query->where("id", $type);
        }
        if($price_per_night){
            $query = $query->where("price_per_night", "like", "%$price_per_night%");
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
}
