<?php

namespace App\Http\Controllers\Custommer;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoomTypeResource;
use App\Models\RommsType;
use Illuminate\Http\Request;

class RommTypeController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->input('type');
        $price_per_night = $request->input('price_per_night');


        $query = RommsType::select("id", "type", "price_per_night", "description", "defaul_people", "description_detail", "title")
        ->with([
            "roomImages:description,image_url,room_type_id,id",
            "reviews:id,rating,comment,room_type_id,user_id",
            "reviews.user:id,name,email,image" 
        ]);
        
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
        
        return response()->json(["data" => RoomTypeResource::collection($dataRoomType)], 200);
    }
    public function getById($id)
    {

        $query = RommsType::select("id", "type", "price_per_night", "description", "defaul_people", "description_detail", "title")
        ->with([
            "roomImages:description,image_url,room_type_id,id",
            "reviews:id,rating,comment,room_type_id,user_id",
            "reviews.user:id,name,email,image" 
        ]);
    
        
        $query = $query->where("id", $id);
        $dataRoomType = $query->first();

        return response()->json(["data" => new RoomTypeResource($dataRoomType)], 200);
    }
}
