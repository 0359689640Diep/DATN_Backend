<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens; 
use Illuminate\Notifications\Notifiable;

class users extends Model
{
    use HasFactory;
    use HasApiTokens, Notifiable;

    protected $fillable = ["name", "email", "password", "image", "role", "phone", "address", "number_CCCD", "status_id"];
    protected $table = "users";
    public function status(){
        return $this->belongsTo(StatusModel::class, "status_id");
    }
}
