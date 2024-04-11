<?php

namespace App\OpenApi\RequestBodies;

use App\OpenApi\Schemas\EmailSchema;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody;
use Vyuldashev\LaravelOpenApi\Factories\RequestBodyFactory;

class DeleteWaitListRequestBody extends RequestBodyFactory
{
  public function build(): RequestBody
  {
    return RequestBody::create('DeleteWaitListRequestBody')
      ->description('대기열 삭제')
      ->content(
        MediaType::json()->schema(EmailSchema::ref())
      );
  }
}
