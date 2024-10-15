<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reviews extends Model
{
    use HasFactory;
    protected $table = 'reviews';
    protected $fillable = ['user_id', 'room_type_id', 'status_id', 'rating', "comment", "bookings_id"];

    public function roomType(){
        return $this->belongsTo(RommsType::class, "room_type_id");
    }
    public function user()
    {
        return $this->belongsTo(users::class, "user_id");
    }
    
}
