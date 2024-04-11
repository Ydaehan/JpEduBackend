<?php

namespace App\OpenApi\RequestBodies;

use App\OpenApi\Schemas\TranslateResultSchema;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody;
use Vyuldashev\LaravelOpenApi\Factories\RequestBodyFactory;

class PronunciationTranslateResultRequestBody extends RequestBodyFactory
{
  public function build(): RequestBody
  {
    return RequestBody::create("PronunciationTranslateResultRequestBody")
      ->description('Ko->ja 번역 텍스트 받기')
      ->content(
        MediaType::json()->schema(TranslateResultSchema::ref())
      );
  }
}
