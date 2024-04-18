<?php

namespace App\OpenApi\Responses;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;

class StoreSuccessResponse extends ResponseFactory
{
  public function build(): Response
  {
    $response = Schema::object('StoreSuccess')->properties(
      Schema::integer('status_code')->example(201),
      Schema::string('message')->example('생성/등록/수정 요청 성공'),
    );

    return Response::create('StoreSuccess')
      ->description('생성/등록/수정 요청 성공')
      ->content(MediaType::json()->schema($response));
  }
}
