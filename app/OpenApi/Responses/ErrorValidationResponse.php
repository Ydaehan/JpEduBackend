<?php

namespace App\OpenApi\Responses;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Contracts\Reusable;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;

class ErrorValidationResponse extends ResponseFactory implements Reusable
{
    public function build(): Response
    {
        $response = Schema::object('ErrorValidation')->properties(
            Schema::integer('status_code')->example(422),
            Schema::string('message')->example('ErrorValidation'),
            Schema::object('errors')
                ->additionalProperties(
                    Schema::array()->items(Schema::string())
                )
                ->example(['field' => ['요청은 잘 만들어졌지만, 문법 오류로 인하여 따를 수 없습니다.']])
        );
        return Response::create('ErrorValidation')
            ->description('요청은 잘 만들어졌지만, 문법 오류로 인하여 따를 수 없습니다.')
            ->content(MediaType::json()->schema($response));
    }
}
