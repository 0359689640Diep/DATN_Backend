<?php

namespace App\Http\Controllers\Custommer;

use App\Http\Controllers\Controller;
use App\Models\ServiceModel;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function getServiceByIdRoomType($id){
        $data = ServiceModel::where("room_type_id", "=", $id)->where("status_id", "=", 1)->get();
        return response()->json(["data" => $data], 200);
    }
}
