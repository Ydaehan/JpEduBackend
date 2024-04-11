<?php

namespace App\OpenApi\RequestBodies;

use App\OpenApi\Schemas\ExcelSchema;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody;
use Vyuldashev\LaravelOpenApi\Factories\RequestBodyFactory;

class StoreExcelVocaRequestBody extends RequestBodyFactory
{
	public function build(): RequestBody
	{
		return RequestBody::create('StoreExcelVoca')
			->description('Excel로 단어장 생성')
			->content(
				MediaType::formData()->schema(ExcelSchema::ref())
			);
	}
}
