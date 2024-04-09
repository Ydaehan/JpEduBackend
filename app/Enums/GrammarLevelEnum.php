<?php

namespace App\Enums;

enum GrammarLevelEnum: string
{
	case N1 = 'N1';
	case N2 = 'N2';
	case N3 = 'N3';
	case N4 = 'N4';
	case N5 = 'N5';

	public function description(): string
	{
		return match ($this) {
			self::N1 => 'N1 문법',
			self::N2 => 'N2 문법',
			self::N3 => 'N3 문법',
			self::N4 => 'N4 문법',
			self::N5 => 'N5 문법'
		};
	}
}
