<?php

namespace App\OpenApi\RequestBodies;

use App\OpenApi\Schemas\GameResultSchema;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody;
use Vyuldashev\LaravelOpenApi\Factories\RequestBodyFactory;

class GameResultRequestBody extends RequestBodyFactory
{
  public function build(): RequestBody
  {
    return RequestBody::create('GameResultRequestBody')
      ->description('게임 결과')
      ->content(
        MediaType::json()
          ->schema(GameResultSchema::ref())
      );
  }
}
