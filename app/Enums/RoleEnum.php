<?php

namespace App\Enums;

enum RoleEnum: string
{
  case User = 'user';
  case Manager = 'manager';
  case Admin = 'admin';

  public function description(): string
  {
    return match ($this) {
      self::User => '일반 사용자 - 게시글 혹은 댓글에 대한 Create, Read 가능 및 컨텐츠 이용 가능',
      self::Manager => '매니저 - 일반 사용자에 대한 CRUD 불가능, 일반 사용자의 게시글 혹은 댓글에 대한 Update, Delete 가능',
      self::Admin => '관리자 - 매니저와 일반 사용자에 대한 CRUD 가능'
    };
  }
}
