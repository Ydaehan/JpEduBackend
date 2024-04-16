<?php

namespace App\Models;

use App\Enums\GrammarLevelEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grammar extends Model
{
  use HasFactory;

  protected $fillable = [
    'grammar',
    'explain',
    'example',
    'mean',
    'conjunction',
    'tier',
  ];

  protected $casts = [
    'tier' => GrammarLevelEnum::class,
  ];

  public function grammarExamples(): HasMany
  {
    return $this->hasMany(GrammarExample::class);
  }

  public function level(): BelongsTo
  {
    return $this->belongsTo(Level::class);
  }
}
