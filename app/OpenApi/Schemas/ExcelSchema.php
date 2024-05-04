<?php

namespace App\OpenApi\Schemas;

use GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract;
use GoldSpecDigital\ObjectOrientedOAS\Objects\AllOf;
use GoldSpecDigital\ObjectOrientedOAS\Objects\AnyOf;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Not;
use GoldSpecDigital\ObjectOrientedOAS\Objects\OneOf;
// use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use App\Http\Responses\MyCustomSchema as Schema;
use Vyuldashev\LaravelOpenApi\Contracts\Reusable;
use Vyuldashev\LaravelOpenApi\Factories\SchemaFactory;

class ExcelSchema extends SchemaFactory implements Reusable
{
	/**
	 * @return AllOf|OneOf|AnyOf|Not|Schema
	 */
	public function build(): SchemaContract
	{
		return Schema::object('StoreExcelVoca')
			->properties(Schema::file('excel')->example('excel.xlsx')->title('엑셀 파일')->description('엑셀 파일을 첨부합니다.'));
	}
}
