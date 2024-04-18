<?php

namespace App\OpenApi\RequestBodies;

use App\OpenApi\Schemas\UpdateSchema;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody;
use Vyuldashev\LaravelOpenApi\Factories\RequestBodyFactory;

class UserUpdateRequestBody extends RequestBodyFactory
{
	public function build(): RequestBody
	{
		return RequestBody::create("UserUpdateRequestBody")
			->description('회원 정보 수정')
			->content(
				MediaType::json()->schema(UpdateSchema::ref())
			);
	}
}
