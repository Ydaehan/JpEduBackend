<?php

namespace App\OpenApi\Parameters;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\ParametersFactory;

class VocabularyLevelParameters extends ParametersFactory
{
  /**
   * @return Parameter[]
   */
  public function build(): array
  {
    return [
      Parameter::header()
        ->name('AccessToken')
        ->description('Access Token')
        ->required(true)
        ->example('Bearer {access_token}')
        ->schema(Schema::string()),
      Parameter::path()
        ->name('levelId')
        ->description('단어장 급수')
        ->required(true)
        ->example('1')
        ->schema(Schema::string()),
    ];
  }
}
