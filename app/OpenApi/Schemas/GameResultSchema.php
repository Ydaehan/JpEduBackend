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

class GameResultSchema extends SchemaFactory implements Reusable
{
	/**
	 * @return AllOf|OneOf|AnyOf|Not|Schema
	 */
	public function build(): SchemaContract
	{
		return Schema::object('GameResult')->properties(
			Schema::integer('score')->example(100)->title('게임 점수'),
			Schema::object('kanji')->items(Schema::string())->example(['漢字1', '漢字2', '...'])->title('오답 한자 리스트'),
			Schema::object('gana')->items(Schema::string())->example(['かな1', 'かな2', '...'])->title('오답 히라가나/카타카나 리스트'),
			Schema::object('meaning')->items(Schema::string())->example(['의미1', '의미2', '...'])->title('오답 의미 리스트'),
			Schema::integer('level_id')->example(8)->title('플레이한 난이도 1:N1, 2:N2, 3:N3, 4:N4, 5:N5, 6:종합, 7:전체, 8:오답노트 중 하나를 선택'),
			Schema::string('category')->example('category')->title('카테고리')
		)->required('score', 'kanji', 'gana', 'meaning', 'level_id');
	}
}
