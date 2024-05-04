<?php

namespace App\OpenApi\RequestBodies;

use App\OpenApi\Schemas\ImageSchema;
// use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use App\Http\Responses\MyCustomMediaType as MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody;
use Vyuldashev\LaravelOpenApi\Factories\RequestBodyFactory;

class ImageRequestBody extends RequestBodyFactory
{
	public function build(): RequestBody
	{
		return RequestBody::create('Image')
			->description('이미지 파일 등록')
			->content(
				MediaType::formData()
					->schema(ImageSchema::ref())
			);
	}
}
