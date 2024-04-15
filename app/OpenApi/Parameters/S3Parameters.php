<?php

namespace App\OpenApi\Parameters;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\ParametersFactory;

class S3Parameters extends ParametersFactory
{
	/**
	 * @return Parameter[]
	 */
	public function build(): array
	{
		return [
			Parameter::query()
				->name('path')
				->description('s3의 경로를 의미합니다.')
				->required(true)
				->example('mobile-images')
				->schema(Schema::string()),
		];
	}
}
