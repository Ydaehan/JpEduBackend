<?php

namespace App\OpenApi\Responses;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Contracts\Reusable;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;

class LoginResponse extends ResponseFactory implements Reusable
{
	public function build(): Response
	{
		$response = Schema::object()->properties(
			Schema::integer('status_code')->example(200),
			Schema::string('message')->example('로그인 성공'),
		);

		return Response::create(self::class)
			->description('로그인')
			->content(MediaType::json()->schema($response));
	}
}
