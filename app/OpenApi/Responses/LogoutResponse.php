<?php

namespace App\OpenApi\Responses;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Contracts\Reusable;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;

class LogoutResponse extends ResponseFactory implements Reusable
{
	public function build(): Response
	{
		$response = Schema::object('Logout')->properties(
			Schema::integer('status_code')->example(200),
			Schema::string('message')->example('로그아웃 성공'),
		);

		return Response::create('Logout')
			->description('로그아웃')
			->content(MediaType::json()->schema($response));
	}
}
