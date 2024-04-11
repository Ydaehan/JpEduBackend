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

class ApplyToManagerSchema extends SchemaFactory implements Reusable
{
	/**
	 * @return AllOf|OneOf|AnyOf|Not|Schema
	 */
	public function build(): SchemaContract
	{
		return Schema::object('ApplyToManager')->properties(
			Schema::string('email')->example('testuser123@naver.com')->title('매니저 지원할 유저 이메일'),
			Schema::string('content')->example('매니저 지원 글')->title('신청 내용'),
		)->required('email', 'content');
	}
}
