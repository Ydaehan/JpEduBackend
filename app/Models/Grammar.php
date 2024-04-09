<?php

namespace App\Models;

use App\Enums\GrammarLevelEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

	public function userGrammarExamples()
	{
		return $this->hasMany(UserGrammarExample::class);
	}
}
