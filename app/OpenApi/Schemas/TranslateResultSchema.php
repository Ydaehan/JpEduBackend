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

class TranslateResultSchema extends SchemaFactory implements Reusable
{
  /**
   * @return AllOf|OneOf|AnyOf|Not|Schema
   */
  public function build(): SchemaContract
  {
    return Schema::object('translateResult')
      ->properties(
        Schema::string('text')->example('안녕하세요')->title('번역할 텍스트'),
      );
  }
}
