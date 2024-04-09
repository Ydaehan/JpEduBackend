<?php

namespace App\Models;

use App\Enums\LevelEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Level extends Model
{
  use HasFactory;

  protected $fillable = [
    'level',
  ];

  protected $casts = [
    'level' => LevelEnum::class,
  ];

  public function score(): BelongsTo
  {
    return $this->belongsTo(Score::class);
  }

  public function vocabularyNotes(): HasMany
  {
    return $this->hasMany(VocabularyNote::class);
  }
}
