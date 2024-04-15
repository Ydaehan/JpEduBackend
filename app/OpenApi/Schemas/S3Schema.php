<?php

namespace App\OpenApi\Schemas;

use GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract;
use GoldSpecDigital\ObjectOrientedOAS\Objects\AllOf;
use GoldSpecDigital\ObjectOrientedOAS\Objects\AnyOf;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Not;
use GoldSpecDigital\ObjectOrientedOAS\Objects\OneOf;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Contracts\Reusable;
use Vyuldashev\LaravelOpenApi\Factories\SchemaFactory;

class S3Schema extends SchemaFactory implements Reusable
{
  /**
   * @return AllOf|OneOf|AnyOf|Not|Schema
   */
  public function build(): SchemaContract
  {
    return Schema::object('S3Upload')->properties(
      Schema::string('file')->example('file-name')->title('파일명'),
      Schema::string('path')->example('achievements')->title('경로'),
    );
  }
}
