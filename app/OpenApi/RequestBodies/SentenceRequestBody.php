<?php

namespace App\OpenApi\RequestBodies;

use App\OpenApi\Schemas\SentenceSchema;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody;
use Vyuldashev\LaravelOpenApi\Factories\RequestBodyFactory;

class SentenceRequestBody extends RequestBodyFactory
{
	public function build(): RequestBody
	{
		return RequestBody::create('Sentence')
			->description('문장 생성')
			->content(
				MediaType::json()->schema(SentenceSchema::ref())
			);
	}
}
