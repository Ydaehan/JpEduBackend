<?php

namespace App\Enums;

enum LevelEnum: string
{
  case N1 = 'n1';
  case N2 = 'n2';
  case N3 = 'n3';
  case N4 = 'n4';
  case N5 = 'n5';
  case Total = 'Total';
  case UserCustom = 'UserCustom';
  case Review = 'Review';

  public function description(): string
  {
    return match ($this) {
      self::N1 => 'N1 난이도',
      self::N2 => 'N2 난이도',
      self::N3 => 'N3 난이도',
      self::N4 => 'N4 난이도',
      self::N5 => 'N5 난이도',
      self::Total => '통합 난이도',
      self::UserCustom => '사용자 지정 난이도',
      self::Review => '복습 난이도'
    };
  }
}
