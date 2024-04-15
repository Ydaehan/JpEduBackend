<?php

namespace App\OpenApi\Responses;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Contracts\Reusable;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;

class UnauthorizedResponse extends ResponseFactory implements Reusable
{
  public function build(): Response
  {
    $response = Schema::object('Unauthorized')->properties(
      Schema::integer('status_code')->example(401),
      Schema::string('message')->example('401: Unauthorized'),
    );

    return Response::create('Unauthorized')
      ->description('올바르지 않은 요청으로 유저가 인증 받지 못했음을 나타냅니다.')
      ->content(MediaType::json()->schema($response));
  }
}
