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

class UpdateSentenceNoteSchema extends SchemaFactory implements Reusable
{
	/**
	 * @return AllOf|OneOf|AnyOf|Not|Schema
	 */
	public function build(): SchemaContract
	{
		return Schema::object('UpdateSentenceNote')->properties(
			Schema::string('title')->example('おはようございます')->title('제목'),
			Schema::array('sentences')->items(
				Schema::object()->properties(
					Schema::string('문장')->example('おはようございます')->title('문장'),
					Schema::string('의미')->example('안녕하세요')->title('의미'),
				)
			),
			Schema::string('situation')->example('출근')->title('상황'),
		);
	}
}
