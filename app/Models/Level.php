<?php

namespace App\Models;

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

  public function ranking(): BelongsTo
  {
    return $this->belongsTo(Ranking::class);
  }

  public function vocabularyNotes(): HasMany
  {
    return $this->hasMany(VocabularyNote::class);
  }
}
