<?php

namespace App\Http\Responses;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;

class MyCustomMediaType extends MediaType
{
	const MEDIA_TYPE_APPLICATION_FORM_DATA = 'multipart/form-data';

	/**
	 * @param string|null $objectId
	 * @return static
	 */
	public static function formData(string $objectId = null): self
	{
		return static::create($objectId)
			->mediaType(static::MEDIA_TYPE_APPLICATION_FORM_DATA);
	}
}
