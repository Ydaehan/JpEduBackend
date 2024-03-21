<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class Manager extends Model
{
    use HasFactory, HasApiTokens, Notifiable;

    protected $fillable = [
        'nickname',
        'password',
        'email',
        'phone',
        'birthday',
        'role'
    ];

    public function jlptQuestions()
    {
        return $this->hasMany(JlptQuestion::class);
    }
}