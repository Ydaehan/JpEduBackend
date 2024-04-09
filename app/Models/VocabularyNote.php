<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VocabularyNote extends Model
{
  use HasFactory;

  protected $fillable = [
    'title', 'user_id', 'meaning', 'gana', 'kanji', 'is_public', 'is_creator', 'level_id',
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

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function level()
  {
    return $this->belongsTo(Level::class);
  }
}
