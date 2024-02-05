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
        return $this->belongsTo(User::class, "author");
    }

    public function category(){
        return $this->belongsTo(Category::class, "category");
    }

    public function pictures(){
        return $this->hasMany(Picture::class,"announcement","id");
    }

    public function notes()
    {
        return $this->hasMany(Note::class, "announcement", "id");
    }

   

}
