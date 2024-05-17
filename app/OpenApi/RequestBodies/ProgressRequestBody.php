<?php

namespace App\OpenApi\RequestBodies;

use App\OpenApi\Schemas\ProgressSchema;
use GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody;
use Vyuldashev\LaravelOpenApi\Factories\RequestBodyFactory;
use App\Http\Responses\MyCustomMediaType as MediaType;


class ProgressRequestBody extends RequestBodyFactory
{
  public function build(): RequestBody
  {
    return RequestBody::create('ProgressRequestBody')
      ->description('학습 진행도를 저장합니다.')
      ->content(MediaType::json()->schema(ProgressSchema::ref()));
  }
}
