<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomModel extends Model
{
    use HasFactory;
    protected $table = "rooms";
    protected $fillable = ["id", "room_type_id", "number", "price_per_night", "description", "defaul_people", "status_id"];
    public function roomType(){
        return $this->belongsTo(RommsType::class, "room_type_id");
    }
    public function status(){
        return $this->belongsTo(StatusModel::class, "status_id");
    }
}
