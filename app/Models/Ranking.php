<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ranking extends Model
{
  use HasFactory;

  protected $fillable = [
    'user_id',
    'category_id',
    'score',
    'solution_time',
    'level_id',
  ];

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  public function level()
  {
    return $this->hasOne(Level::class);
  }
}
