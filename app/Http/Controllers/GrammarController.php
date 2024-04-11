<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grammar;
use App\OpenApi\Parameters\AccessTokenParameters;
use App\OpenApi\Parameters\DeleteSentenceParameters;
use App\OpenApi\Parameters\JlptTierParameters;
use App\OpenApi\RequestBodies\StoreGrammarRequestBody;
use App\OpenApi\Responses\BadRequestResponse;
use App\OpenApi\Responses\ErrorValidationResponse;
use App\OpenApi\Responses\StoreSuccessResponse;
use App\OpenApi\Responses\SuccessResponse;
use App\OpenApi\Responses\UnauthorizedResponse;
use Illuminate\Support\Facades\Validator;
use Vyuldashev\LaravelOpenApi\Attributes as OpenApi;

#[OpenApi\PathItem]
class GrammarController extends Controller
{
    /**
     * JLPT 문법을 등급별로 조회
     *
     * JLPT 문법을 등급별로 조회합니다.
     * */
    #[OpenApi\Operation(tags: ['Grammar'], method: 'GET')]
    #[OpenApi\Parameters(factory: JlptTierParameters::class)]
    #[OpenApi\Response(factory: SuccessResponse::class, statusCode: 200, description: '조회 성공')]
    #[OpenApi\Response(factory: BadRequestResponse::class, statusCode: 400, description: '조회 실패')]
    #[OpenApi\Response(factory: UnauthorizedResponse::class, statusCode: 401, description: '토큰 인증 실패')]
    #[OpenApi\Response(factory: ErrorValidationResponse::class, statusCode: 422, description: '유효성 검사 실패')]
    public function show($tier)
    {
        $validator = Validator::make(['tier' => $tier], [
            'tier' => 'required|in:N1,N2,N3,N4,N5'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $grammars = Grammar::where('tier', $tier)->get();

        // 'tier' 필드를 제외하고 반환
        $grammars = $grammars->map(function ($grammar) {
            unset($grammar->tier);
            return $grammar;
        });

        return response()->json($grammars);
    }

    /**
     * JLPT 문법 생성
     *
     * JLPT 문법을 생성합니다.
     */
    #[OpenApi\Operation(tags: ['Grammar'], method: 'POST')]
    #[OpenApi\Parameters(factory: AccessTokenParameters::class)]
    #[OpenApi\RequestBody(factory: StoreGrammarRequestBody::class)]
    #[OpenApi\Response(factory: StoreSuccessResponse::class, description: '생성/등록/수정 요청 성공', statusCode: 201)]
    #[OpenApi\Response(factory: BadRequestResponse::class, statusCode: 400, description: '오류 발생')]
    public function create(Request $request)
    {
        // request에 json 타입의 파일을 받음
        try {
            $json = json_decode($request->file('file')->get(), true);
            // json 파일을 받아서 DB에 저장
            foreach ($json['data'] as $item) {
                $grammar = new Grammar();
                $grammar->grammar = $item['Grammar'];
                $grammar->explain = $item['Description'];
                // 5번 반복하면 $index 를 1씩 증가시키며 $item['Example' . $index]를 가져와서 json 형식으로 저장
                $example = [];
                for ($index = 1; $index <= 5; $index++) {
                    if (isset($item['Example' . $index]) && $item['Example' . $index] != "") {
                        $example[$index] = $item['Example' . $index];
                    } else {
                        $example[$index] = null;
                    }
                }
                $grammar->example = json_encode($example);
                $grammar->mean = $item['Meaning'];
                $grammar->conjunction = $item['Connection'];
                $grammar->tier = $json['tier'];
                $grammar->save();
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * JLPT 문법 삭제
     *
     * JLPT 문법을 삭제합니다.<br/>
     * 관리자만 JLPT 문법을 삭제 할 수 있습니다.
     */
    #[OpenApi\Operation(tags: ['Grammar'], method: 'DELETE')]
    #[OpenApi\Parameters(factory: DeleteSentenceParameters::class)]
    #[OpenApi\Response(factory: SuccessResponse::class, statusCode: 200, description: '삭제 성공')]
    #[OpenApi\Response(factory: BadRequestResponse::class, statusCode: 400, description: '삭제 실패')]
    public function delete($id)
    {
        $grammar = Grammar::find($id);
        if ($grammar) {
            $grammar->delete();
            return response()->json(['message' => 'success']);
        } else {
            return response()->json(['error' => 'not found'], 404);
        }
    }
}
