<?php

namespace App\OpenApi\RequestBodies;

use App\OpenApi\Schemas\S3Schema;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody;
use Vyuldashev\LaravelOpenApi\Factories\RequestBodyFactory;

class S3RequestBody extends RequestBodyFactory
{
  public function build(): RequestBody
  {
    return RequestBody::create('S3RequestBody')
      ->description('s3 파일 업로드')
      ->content(
        MediaType::json()->schema(S3Schema::ref())
      );
  }
}
