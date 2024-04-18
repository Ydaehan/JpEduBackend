<?php

namespace App\OpenApi\Responses;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Contracts\Reusable;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;

class SuccessResponse extends ResponseFactory implements Reusable
{
  public function build(): Response
  {
    $response = Schema::object('Success')->properties(
      Schema::integer('status_code')->example(200),
      Schema::string('message')->example('요청 성공'),
    );

    return Response::create('Success')
      ->description('요청 성공')
      ->content(MediaType::json()->schema($response));
  }
}
