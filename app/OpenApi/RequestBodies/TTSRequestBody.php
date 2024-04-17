<?php

namespace App\OpenApi\RequestBodies;

use App\OpenApi\Schemas\TTSSchema;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody;
use Vyuldashev\LaravelOpenApi\Factories\RequestBodyFactory;

class TTSRequestBody extends RequestBodyFactory
{
  public function build(): RequestBody
  {
    return RequestBody::create('TTSRequestBody')
      ->description('텍스트를 음성으로 변환합니다.')
      ->content(MediaType::json()->schema(TTSSchema::ref()));
  }
}
