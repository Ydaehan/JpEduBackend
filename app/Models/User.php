<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\RoleEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
  use HasApiTokens, HasFactory, Notifiable;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'nickname',
    'email',
    'password',
    'phone',
    'birthday',
    'role',
    // 'verification_code'
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = [
    'password',
    'remember_token',
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'email_verified_at' => 'datetime',
    'password' => 'hashed',
    'role' => RoleEnum::class,
  ];

  public function socialAccounts(): HasMany
  {
    return $this->hasMany(SocialAccount::class);
  }

  public function vocabularyNotes(): HasMany
  {
    return $this->hasMany(VocabularyNote::class);
  }

  public function userSetting(): HasOne
  {
    return $this->hasOne(UserSetting::class);
  }

  public function scores(): HasMany
  {
    return $this->hasMany(Score::class);
  }

  public function dailyChecks(): HasMany
  {
    return $this->hasMany(DailyCheck::class);
  }

  public function subscribers(): HasMany
  {
    return $this->hasMany(Subscription::class, 'subscriber_id');
  }

  public function targets(): HasMany
  {
    return $this->hasMany(Subscription::class, 'target_id');
  }

  public function jlptQuestions(): HasMany
  {
    return $this->hasMany(JlptQuestion::class);
  }

  public function userGrammarExamples(): HasMany
  {
    return $this->hasMany(UserGrammarExample::class);
  }

  public function sentences(): HasMany
  {
    return $this->hasMany(Sentence::class);
  }

  public function achievements(): BelongsToMany
  {
    return $this->belongsToMany(Achievement::class);
  }
}
