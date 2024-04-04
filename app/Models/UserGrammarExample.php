<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserGrammarExample extends Model
{
  use HasFactory;

  protected $fillable = [
    'grammar_id',
    'user_id',
    'example',
  ];

  public function grammar(): BelongsTo
  {
    return $this->belongsTo(Grammar::class);
  }

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }
}
