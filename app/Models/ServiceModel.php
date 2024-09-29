<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceModel extends Model
{
    use HasFactory;
    protected $table = 'service';
    protected $fillable = [
        'name',
        'description',
        'image',
        'price',
        "room_type_id",
        "status_id"
    ];
    public function status(){
        return $this->belongsTo(StatusModel::class, "status_id");
    }
    public function roomType(){
        return $this->belongsTo(RommsType::class, "room_type_id");
    }
}
