<?php

namespace App\OpenApi\RequestBodies;

use App\OpenApi\Schemas\SentenceNoteSchema;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody;
use Vyuldashev\LaravelOpenApi\Factories\RequestBodyFactory;

class StoreSentenceNotesRequestBody extends RequestBodyFactory
{
	public function build(): RequestBody
	{
		return RequestBody::create('StoreSentences')
			->description('타자 연습 문장 생성')
			->content(
				MediaType::json()->schema(SentenceNoteSchema::ref())
			);
	}
}
