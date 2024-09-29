<?php

namespace App\Http\Controllers;

use App\Models\StatusModel;
use Illuminate\Http\Request;

class StatusController extends Controller
{
    public function index(){
        $data = StatusModel::all();
        if ($data->isEmpty()) {
            // Nếu dữ liệu trống, trả về 404 với thông báo lỗi
            return response()->json(['error' => 'Không có dữ liệu'], 404);
        }
        return response()->json(compact("data"), 200);
    }
}
