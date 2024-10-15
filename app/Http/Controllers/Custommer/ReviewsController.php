<?php

namespace App\Http\Controllers\Custommer;

use App\Http\Controllers\Controller;
use App\Models\BookingModel;
use App\Models\Reviews;
use App\Models\token;
use Illuminate\Http\Request;

class ReviewsController extends Controller
{
    private $dateNow;
    public function __construct()
    {
        $this->dateNow = date('Y-m-d H:i:s');
    }
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
    public function getByIdBooking($bookingsId)
    {

        $query = Reviews::select("id", "rating", "comment", "created_at", "room_type_id", "user_id")->with("user:id,name,image");
        
        $query = $query->where("bookings_id", $bookingsId);
        $data = $query->first();

        return response()->json(["data" => $data], 200);
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

    public function postReviews(Request $request){
        $bookingsId = $request->input("bookings_id");
        $rating = $request->input("rating");
        $comment = $request->input("comment");

        $dataBooking = BookingModel::where("id", $bookingsId)->with("roomType:id,type", )->first();
        $token = $request->bearerToken();
        $dataToken = token::where("value", $token)->first();
        // Lấy ID của người dùng
        if (!$dataBooking && !$dataToken) {
            return response()->json(["message" => "Không tìm thấy đặt phòng"], 400);
        }
        $userId = $dataToken->user_id;
        Reviews::insert([
            "user_id" => $userId,
            "room_type_id" => $dataBooking->roomType->id,
            "status_id" => 1,
            "rating" => $rating,
            "comment" => $comment,
            "bookings_id" => $bookingsId,
            "created_at" => $this->dateNow,
        ]);
        return response()->json(["message" => "Cảm ơn bạn đã góp ý cho chúng tôi"], 200);
    }
}
