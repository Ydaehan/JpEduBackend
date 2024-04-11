<?php

namespace App\OpenApi\Parameters;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\ParametersFactory;

class JlptTierParameters extends ParametersFactory
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
      Parameter::query()
        ->name('tier')
        ->description('JLPT Tier')
        ->required(true)
        ->example('N1, N2, N3, N4, N5')
        ->schema(Schema::string()),
    ];
  }
}
