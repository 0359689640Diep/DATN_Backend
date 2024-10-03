<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RommsType extends Model
{
    use HasFactory;
    protected $table = 'rooms_type';
    protected $fillable = ['type', 'price_per_night', 'defaul_people', 'description'];

    public function roomImages(){
        return $this->hasMany(RommsImage::class, "room_type_id");
    }
}
