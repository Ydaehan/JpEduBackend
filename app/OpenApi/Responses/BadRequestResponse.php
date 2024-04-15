<?php

namespace App\OpenApi\Responses;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Contracts\Reusable;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;

class BadRequestResponse extends ResponseFactory implements Reusable
{
    public function build(): Response
    {
        $response = Schema::object('BadRequest')->properties(
            Schema::integer('status_code')->example(400),
            Schema::string('message')->example('BadRequest'),
            Schema::object('errors')
                ->additionalProperties(
                    Schema::array()->items(Schema::string())
                )
                ->example(['field' => ['400 Bad Request']])
        );
        return Response::create('BadRequest')
            ->description('이 응답은 잘못된 문법으로 인하여 서버가 요청을 이해할 수 없음을 의미합니다.')
            ->content(MediaType::json()->schema($response));
    }
}
