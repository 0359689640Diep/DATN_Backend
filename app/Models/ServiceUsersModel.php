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
    public function service(){
        return $this->belongsTo(ServiceModel::class, "service_id");
    }
    public function user()
    {
        return $this->belongsTo(users::class, "user_id");
    }
    public function status(){
        return $this->belongsTo(StatusModel::class, "status_id");
    }
}
