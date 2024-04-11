<?php

namespace App\OpenApi\RequestBodies;

use App\OpenApi\Schemas\SentenceSchema;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody;
use Vyuldashev\LaravelOpenApi\Factories\RequestBodyFactory;

class SentenceUpdateRequestBody extends RequestBodyFactory
{
  public function build(): RequestBody
  {
    return RequestBody::create('SentenceUpdate')
      ->description('문장 수정')
      ->content(
        MediaType::json()->schema(SentenceSchema::ref())
      );
  }
}
