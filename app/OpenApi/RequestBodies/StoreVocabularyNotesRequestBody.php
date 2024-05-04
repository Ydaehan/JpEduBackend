<?php

namespace App\OpenApi\RequestBodies;

use App\OpenApi\Schemas\VocabularyNotesSchema;
use App\Http\Responses\MyCustomMediaType as MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody;
use Vyuldashev\LaravelOpenApi\Factories\RequestBodyFactory;

class StoreVocabularyNotesRequestBody extends RequestBodyFactory
{
	public function build(): RequestBody
	{
		return RequestBody::create('StoreVocabularyNotes')
			->description('단어장 생성')
			->content(
				MediaType::formData()->schema(VocabularyNotesSchema::ref())
			);
	}
}
