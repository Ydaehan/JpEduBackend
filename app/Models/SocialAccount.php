<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialAccount extends Model
{
  use HasFactory;


  protected $fillable = [
    'user_id', 'provider_name', 'provider_id', 'nickname', 'name', 'email',  'avatar',
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }
}
