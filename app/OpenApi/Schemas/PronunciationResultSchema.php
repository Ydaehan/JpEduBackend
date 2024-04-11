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

class PronunciationResultSchema extends SchemaFactory implements Reusable
{
  /**
   * @return AllOf|OneOf|AnyOf|Not|Schema
   */
  public function build(): SchemaContract
  {
    return Schema::object('PronunciationResultSchema')->properties(
      Schema::file('audio')->example('testAudio.wav')->title('녹음된 음성 파일'),
    );
  }
}
