<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RommsType extends Model
{
    use HasFactory;
    protected $table = 'rooms_type';
    protected $fillable = ['type', 'price_per_night', 'defaul_people', 'description', "title", "description_detail"];

    public function roomImages(){
        return $this->hasMany(RommsImage::class, "room_type_id");
    }
    public function reviews(){
        return $this->hasMany(Reviews::class, "room_type_id");
    }
}
