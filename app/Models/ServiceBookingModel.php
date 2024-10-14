<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceBookingModel extends Model
{
    use HasFactory;
    protected $table = "service_booking";
    protected $fillable = [
        "booking_id",
        "service_id",
        "status_id",
        "quanlity_service",
        "total_price",
    ];
}
