<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;
    protected $fillable = [
        "title",
        "body",
        "added_at",
        "category",
        "author",
    ];

    public function author(){
        return $this->belongsTo(User::class);
    }

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function pictures(){
        return $this->hasMany(Picture::class,"announcement","id");
    }

}
