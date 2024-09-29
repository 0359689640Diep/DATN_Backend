<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RommsType extends Model
{
    use HasFactory;
    protected $table = 'rooms_type';
    protected $fillable = ["id", "type"];

    public function roomImages(){
        return $this->hasMany(RommsImage::class, "room_type_id");
    }
}
