<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);
    
        // Format room_images
        if (!empty($this->roomImages)) {
            $data['room_images'] = $this->roomImages->map(function ($image) {
                return [
                    'description' => $image->description,
                    'image_url' => url('storage/' . $image->image_url),  // Assuming images are stored in the 'storage' folder
                    'room_type_id' => $image->room_type_id,
                    'id' => $image->id
                ];
            });
        }
    
        $totalScore = 0;
        $quantityReviews = 0;
        if (!empty($this->reviews)) {
    
            // Lấy thông tin reviews và user
            $data['reviews'] = $this->reviews->map(function ($review) use (&$totalScore, &$quantityReviews) {
                $totalScore += $review->rating;
                $quantityReviews++;
    
                // Lấy thông tin người dùng từ quan hệ user
                $user = $review->user;
                return [
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'user' => [
                        'email' => $user->email ?? null,
                        'users_image' => !empty($user->image) ? url('storage/' . $user->image) : null,
                        'name' => $user->name ?? null,
                        'id' => $user->id ?? null
                    ]
                ];
            });
        }
    
        // Tính lượt đánh giá trung bình của từng loại phòng
        $averageRating = $quantityReviews > 0 ? round($totalScore / $quantityReviews, 1) : 0;
        $data['average_rating'] = $averageRating;
    
        return $data;
    }
    
}
