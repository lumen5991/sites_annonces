<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;
    protected $fillable = [
        "note",
        "user",
        "announcement",
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function announcement(){
        return $this->belongsTo(Announcement::class);
    }
}
