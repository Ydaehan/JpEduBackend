<?php

namespace App\OpenApi\RequestBodies;

use App\OpenApi\Schemas\UserSchema;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody;
use Vyuldashev\LaravelOpenApi\Factories\RequestBodyFactory;

class UserStoreRequestBody extends RequestBodyFactory
{
  public function build(): RequestBody
  {
    return RequestBody::create("UserStoreRequestBody")
      ->description('회원가입')
      ->content(
        MediaType::json()->schema(UserSchema::ref())
      );
  }
}
