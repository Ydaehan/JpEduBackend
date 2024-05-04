<?php

namespace App\OpenApi\RequestBodies;

use App\OpenApi\Schemas\StoreGrammarSchema;
use App\Http\Responses\MyCustomMediaType as MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody;
use Vyuldashev\LaravelOpenApi\Factories\RequestBodyFactory;

class StoreGrammarRequestBody extends RequestBodyFactory
{
	public function build(): RequestBody
	{
		return RequestBody::create('StoreGrammarRequestBody')
			->description('Jlpt 문법 생성')
			->content(
				MediaType::formData()->schema(StoreGrammarSchema::ref())
			);
	}
}
