<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSetting extends Model
{
  use HasFactory;

  protected $fillable = [
    'user_id',
    'review_note_auto_register',
    'rank_auto_register',
    'vocabulary_note_auto_visibility',
  ];

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }
}
