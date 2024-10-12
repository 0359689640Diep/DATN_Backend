<?php

namespace App\Http\Controllers\Custommer;

use App\Http\Controllers\Controller;
use App\Models\Reviews;
use Illuminate\Http\Request;

class ReviewsController extends Controller
{
    public function getByIdRoomType($roomTypeId)
    {

        $query = Reviews::select("id", "rating", "comment", "created_at", "room_type_id", "user_id")->with("user:id,name,image");
        
        $query = $query->where("room_type_id", $roomTypeId);
        $data = $query->get();
        if ($data->isEmpty()) {
            return response()->json(['message' => 'Không có dữ liệu'], 404);
        }

        $toalScore = 0;
        $quanlityReviews = 0;

        foreach ($data as $review) {
            $toalScore += $review->rating;
            $quanlityReviews++;
            $review->users_name = $review->user->name;
            $review->users_image = asset('storage/' . $review->user->image);
        }

        // tính lượt đánh giá trung bình của từng loại phòng
        $averageRating = round($toalScore / $quanlityReviews);
    
        return response()->json(["data" => $data, "averageRating" => $averageRating], 200);
    }
    public function getAverageRating($roomTypeId)
    {

        $query = Reviews::select("id", "rating", "comment", "created_at", "room_type_id", "user_id");
        
        $query = $query->where("room_type_id", $roomTypeId);
        $data = $query->get();
        if ($data->isEmpty()) {
            return response()->json(['message' => 'Không có dữ liệu'], 404);
        }

        $toalScore = 0;
        $quanlityReviews = 0;

        foreach ($data as $review) {
            $toalScore += $review->rating;
            $quanlityReviews++;
        }

        // tính lượt đánh giá trung bình của từng loại phòng
        $averageRating = round($toalScore / $quanlityReviews);
    
        return response()->json(["data" => $averageRating], 200);
    }
}
