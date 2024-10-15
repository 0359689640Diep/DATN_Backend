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

    public function service(){
        return $this->belongsTo(ServiceModel::class, "service_id");
    }
    public function status(){
        return $this->belongsTo(StatusModel::class, "status_id");
    }
}
