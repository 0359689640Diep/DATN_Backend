<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentsModel extends Model
{
    use HasFactory;
    protected $fillable = ["id", "booking_id", "status_id", "amount", "payment_date", "payment_method", "code", "payment_gateway_response"];
    protected $table = "payments";
}
