<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JlptQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'manager_id',
        'level_id',
        'title',
        'visibility'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
