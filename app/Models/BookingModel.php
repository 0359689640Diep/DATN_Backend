<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingModel extends Model
{
    use HasFactory;
    protected $table = "bookings";
    protected $fillable = ["user_id", "room_id", "room_type_id", "status_id", "person_in_charge", "check_in_date", "check_out_date", "VAT", "total_price", "actual_number_people", "surcharge", "type", "note",];
    public function payments(){
        return $this->hasMany(PaymentsModel::class, "booking_id");
    }
    public function serviceBooking(){
        return $this->hasMany(ServiceBookingModel::class, "booking_id");
    }
    public function roomType(){
        return $this->belongsTo(RommsType::class, "room_type_id");
    }
    public function room(){
        return $this->belongsTo(RoomModel::class, "room_id");
    }
    public function status(){
        return $this->belongsTo(StatusModel::class, "status_id");
    }
}
