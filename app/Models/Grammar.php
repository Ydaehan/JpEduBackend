<?php

namespace App\Models;

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

	public function userGrammarExamples()
	{
		return $this->hasMany(UserGrammarExample::class);
	}
}
