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

class UpdateVocabularyNoteSchema extends SchemaFactory implements Reusable
{
	/**
	 * @return AllOf|OneOf|AnyOf|Not|Schema
	 */
	public function build(): SchemaContract
	{
		return Schema::object('UpdateVocabularyNote')->properties(
			Schema::string('title')->example('title')->title('단어장 제목'),
			Schema::array('kanji')->example(['한자1,한자2, ...'])->title('단어장 한자')->description('Json array형태로 저장합니다.'),
			Schema::array('gana')->example(['가나1,가나2, ...'])->title('단어장 가나')->description('Json array형태로 저장합니다.'),
			Schema::array('meaning')->example(['의미1,의미2, ...'])->title('단어장 의미')->description('Json array형태로 저장합니다.'),
			Schema::integer('level_id')->example(1)->title('등급')->description('1:N1, 2:N2, 3:N3, 4:N4, 5:N5, 6:종합, 7:전체, 8:오답노트'),
			Schema::boolean('is_public')->example(true)->title('단어장 공개 여부')->description('단어장 공개 여부를 boolean 형태로 저장합니다.')
		)->required('title', 'kanji', 'gana', 'meaning', 'level_id');
	}
}
