<?php

namespace App\OpenApi\Parameters;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\ParametersFactory;

class SocialCallbackParameters extends ParametersFactory
{
  /**
   * @return Parameter[]
   */
  public function build(): array
  {
    return [
      Parameter::path()
        ->name('provider')
        ->description('소셜 로그인 제공자')
        ->required(true)
        ->example('google, naver, kakao, github')
        ->schema(Schema::string()),
      Parameter::query()
        ->name('location.search')
        ->description('url')
        ->required(true)
        ->example('url')
        ->schema(Schema::string()),
    ];
  }
}
