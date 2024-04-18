<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
  use HasFactory;

  protected $fillable = [
    'subscriber_id',
    'target_id',
  ];

  public function subscriber(): BelongsTo
  {
    return $this->belongsTo(User::class, 'subscriber_id');
  }

  public function target(): BelongsTo
  {
    return $this->belongsTo(User::class, 'target_id');
  }
}
