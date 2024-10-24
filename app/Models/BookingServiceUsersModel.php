<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingServiceUsersModel extends Model
{
    use HasFactory;
    protected $table = "booking_service_users";
    protected $fillable = [
        "booking_service_id",
        "user_id",
    ];
}
