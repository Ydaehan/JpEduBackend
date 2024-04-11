<?php

namespace App\OpenApi\Parameters;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\ParametersFactory;

class DeleteSentenceParameters extends ParametersFactory
{
  /**
   * @return Parameter[]
   */
  public function build(): array
  {
    return [
      Parameter::header()
        ->name('AccessToken')
        ->description("Admin'sAccess Token")
        ->required(true)
        ->example('Bearer {관리자 access_token}')
        ->schema(Schema::string()),
      Parameter::query()
        ->name('id')
        ->description('id')
        ->required(true)
        ->example('1')
        ->schema(Schema::integer()),
    ];
  }
}
