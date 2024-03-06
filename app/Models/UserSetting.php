<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'review_note_auto_register',
        'rank_auto_register',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
