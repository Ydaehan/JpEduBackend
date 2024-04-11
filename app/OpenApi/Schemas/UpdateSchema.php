<?php

namespace App\OpenApi\Schemas;

use GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract;
use GoldSpecDigital\ObjectOrientedOAS\Objects\AllOf;
use GoldSpecDigital\ObjectOrientedOAS\Objects\AnyOf;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Not;
use GoldSpecDigital\ObjectOrientedOAS\Objects\OneOf;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Contracts\Reusable;
use Vyuldashev\LaravelOpenApi\Factories\SchemaFactory;

class UpdateSchema extends SchemaFactory implements Reusable
{
	/**
	 * @return AllOf|OneOf|AnyOf|Not|Schema
	 */
	public function build(): SchemaContract
	{
		return Schema::object('UserUpdate')->properties(
			Schema::string('nickname')->example('testUser')->title('유저 닉네임'),
			Schema::string('email')->example('testuser123@naver.com')->title('유저 이메일'),
			Schema::string('password')->example('password123')->title('유저 비밀번호'),
			Schema::string('password_confirmation')->example('password123')->title('유저 비밀번호 확인'),
			Schema::string('phone')->example('01012345678')->title('유저 전화번호'),
			Schema::string('birthday')->example('1999-01-01')->title('유저 생년월일'),
		);
	}
}
