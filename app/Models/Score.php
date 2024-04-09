<?php

namespace App\Models;

use App\Enums\CategoryEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Score extends Model
{
  use HasFactory;

  protected $fillable = [
    'user_id',
    'category',
    'score',
    'solution_time',
    'level_id',
  ];

  protected $casts = [
    'category' => CategoryEnum::class,
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
