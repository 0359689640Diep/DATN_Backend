<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BannerImage extends Model
{
    use HasFactory;
    protected $fillable = ["status_id", "image_url"];
    protected $table = "banner_image";
    public function status(){
        return $this->belongsTo(StatusModel::class, "status_id");
    }
}
