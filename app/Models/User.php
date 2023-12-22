<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $fillable = [
        "firstname",
        "lastname",
        "username",
        "picture",
        "email",
        "password",
        "verification_code",
        "email_verify_at",
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
 
    public function announcement(){
        return $this->hasMany(Announcement::class, "author","id");
    }

    public function note(){
        return $this->hasMany(Note::class, "user","id");
    }
}
