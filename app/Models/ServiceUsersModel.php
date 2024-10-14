<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceUsersModel extends Model
{
    use HasFactory;
    protected $table = "service_users";
    protected $fillable = [
        "service_id",
        "user_id",
        "status_id",
    ];
}
