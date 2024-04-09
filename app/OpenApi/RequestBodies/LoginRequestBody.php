<?php

namespace App\OpenApi\RequestBodies;

use App\OpenApi\Schemas\LoginSchema;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody;
use Vyuldashev\LaravelOpenApi\Factories\RequestBodyFactory;

class LoginRequestBody extends RequestBodyFactory
{
  public function build(): RequestBody
  {
    return RequestBody::create(self::class)
      ->description('로그인')
      ->content(
        MediaType::json()->schema(LoginSchema::ref())
      );
  }
}
