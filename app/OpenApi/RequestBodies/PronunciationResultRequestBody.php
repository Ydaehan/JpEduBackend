<?php

namespace App\OpenApi\RequestBodies;

use App\OpenApi\Schemas\PronunciationResultSchema;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody;
use Vyuldashev\LaravelOpenApi\Factories\RequestBodyFactory;

class PronunciationResultRequestBody extends RequestBodyFactory
{
  public function build(): RequestBody
  {
    return RequestBody::create("PronunciationResultRequestBody")
      ->description('발음평가 결과 받기')
      ->content(
        MediaType::formData()->schema(PronunciationResultSchema::ref())
      );
  }
}
