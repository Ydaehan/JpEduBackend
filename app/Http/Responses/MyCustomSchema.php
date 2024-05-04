<?php

namespace App\Http\Responses;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

class MyCustomSchema extends Schema
{
	const TYPE_FILE = 'file';
	const TYPE_TEXT = 'text';

	// 필요한 메서드와 속성을 추가합니다.
	/**
	 * @param string|null $objectId
	 * @return static
	 */
	public static function file(string $objectId = null): self
	{
		return static::create($objectId)->type(static::TYPE_FILE);
	}

	/**
	 * @param string|null $objectId
	 * @return static
	 */
	public static function text(string $objectId = null): self
	{
		return static::create($objectId)->type(static::TYPE_TEXT);
	}
}
