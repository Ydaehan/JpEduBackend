<?php

namespace App\OpenApi\Responses;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Contracts\Reusable;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;

class ErrorValidationResponse extends ResponseFactory implements Reusable
{
  public function build(): Response
  {
    $response = Schema::object()->properties(
      Schema::integer('status_code')->example(403),
      Schema::string('message')->example('ErrorValidation'),
      Schema::object('errors')
        ->additionalProperties(
          Schema::array()->items(Schema::string())
        )
        ->example(['field' => ['Something is wrong with this field']])
    );
    return Response::create('ErrorValidation')
      ->description('유효성 검사 실패')
      ->content(MediaType::json()->schema($response));
  }
}
