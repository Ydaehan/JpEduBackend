<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JlptQuestion extends Model
{
  use HasFactory;

  protected $fillable = [
    'manager_id',
    'level_id',
    'title',
    'visibility'
  ];

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }
}
