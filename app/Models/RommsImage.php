<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RommsImage extends Model
{
    use HasFactory;
    protected $table = 'rooms_image';
    protected $fillable = ["id", "room_type_id", "image_url", "description"];
    public function roomType(){
        return $this->belongsTo(RommsType::class, "room_type_id");
    }   
}
