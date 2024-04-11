<?php

namespace App\OpenApi\RequestBodies;

use App\OpenApi\Schemas\EmailSchema;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody;
use Vyuldashev\LaravelOpenApi\Factories\RequestBodyFactory;

class SendSignUpEmailRequestBody extends RequestBodyFactory
{
	public function build(): RequestBody
	{
		return RequestBody::create('SendSignUpEmailRequestBody')
			->description('회원가입 이메일 전송')
			->content(
				MediaType::json()->schema(EmailSchema::ref())
			);
	}
}
