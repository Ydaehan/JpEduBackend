<?php

namespace App\OpenApi\Schemas;

use GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract;
use GoldSpecDigital\ObjectOrientedOAS\Objects\AllOf;
use GoldSpecDigital\ObjectOrientedOAS\Objects\AnyOf;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Not;
use GoldSpecDigital\ObjectOrientedOAS\Objects\OneOf;
use App\Http\Responses\MyCustomSchema as Schema;
use Vyuldashev\LaravelOpenApi\Contracts\Reusable;
use Vyuldashev\LaravelOpenApi\Factories\SchemaFactory;

class ImageSchema extends SchemaFactory implements Reusable
{
	/**
	 * @return AllOf|OneOf|AnyOf|Not|Schema
	 */
	public function build(): SchemaContract
	{
		return Schema::object('Image')->properties(
			Schema::file('image')->example('.png, .jpg, .jpeg')->title('이미지 파일'),
		);
	}
}
