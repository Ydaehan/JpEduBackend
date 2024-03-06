<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'kanji',
        'gana',
        'meaning'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
