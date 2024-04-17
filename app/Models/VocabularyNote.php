<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VocabularyNote extends Model
{
  use HasFactory;

  protected $fillable = [
    'title', 'user_id', 'meaning', 'gana', 'kanji', 'is_public', 'is_creator', 'level_id',
  ];

  protected $hidden = [
    'user_id', 'level_id',
    'is_creator',
  ];

  protected $attributes = [
    'is_public' => false,
    'is_creator' => false,
    'level_id' => 7,
  ];

  protected $casts = [
    'meaning' => 'json',
    'gana' => 'json',
    'kanji' => 'json',
  ];

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  public function level(): BelongsTo
  {
    return $this->belongsTo(Level::class);
  }
}
