<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Manager extends Model
{
    use HasFactory;

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
