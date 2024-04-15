<?php

namespace App\Http\Controllers;

use App\OpenApi\Parameters\AccessTokenParameters;
use App\OpenApi\RequestBodies\PronunciationResultRequestBody;
use App\OpenApi\RequestBodies\PronunciationTranslateResultRequestBody;
use App\OpenApi\Responses\BadRequestResponse;
use App\OpenApi\Responses\ErrorValidationResponse;
use App\OpenApi\Responses\SuccessResponse;
use App\OpenApi\Responses\UnauthorizedResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Vyuldashev\LaravelOpenApi\Attributes as OpenApi;

#[OpenApi\PathItem]
class SpeechController extends Controller
{
	/**
	 * 발음평가 결과 받기
	 *
	 * 자신의 발음 녹음본과 비교할 텍스트를 보내면 발음평가 결과를 받을 수 있습니다.
	 */
	#[OpenApi\Operation(tags: ['Speech'], method: 'POST')]
	#[OpenApi\Parameters(factory: AccessTokenParameters::class)]
	#[OpenApi\RequestBody(factory: PronunciationResultRequestBody::class)]
	#[OpenApi\Response(factory: SuccessResponse::class, description: '발음평과 결과 받기 성공', statusCode: 200)]
	#[OpenApi\Response(factory: UnauthorizedResponse::class, description: '인증 오류', statusCode: 401)]
	#[OpenApi\Response(factory: ErrorValidationResponse::class, description: '유효성 검사 오류', statusCode: 422)]
	public function pronunciationAssessment(Request $request)
	{
		//
		try {
			$validator = Validator::make($request->all(), [
				'audio' => 'required|file|mimes:wav',
				'referenceText' => 'required|string',
			]);

			$speechResponse = Http::asMultipart()->post('http://host.docker.internal:5000/speech', [
				[
					'name' => 'audio',
					'contents' => fopen($request->file('audio')->getRealPath(), 'r'),
					'filename' => $request->file('audio')->getClientOriginalName(),
				],
				[
					'name' => 'referenceText',
					'contents' => $request->referenceText,
				],
			])->json();

			return response()->json([
				"status" => "Success",
				"speechResult" => $speechResponse,
				"message" => "발음 평가가 완료되었습니다."
			]);
		} catch (Exception $e) {
			return response()->json([
				"status" => "Fail",
				"message" => "SpeechController: " . $e->getMessage()
			], 400);
		}
	}

	/**
	 * @OA\Post (
	 *     path="/api/speech/translate",
	 *     tags={"Speech"},
	 *     summary="번역 결과 받기",
	 *     description="번역할 텍스트를 보내면 번역 결과를 받을 수 있습니다. ko->ja",
	 *     @OA\Parameter(
	 *         name="Authorization",
	 *         in="header",
	 *         required=true,
	 *         description="Bearer {access_token}",
	 *         @OA\Schema(type="string")
	 *     ),
	 *     @OA\RequestBody(
	 *         description="번역 요구 정보",
	 *         required=true,
	 *         @OA\MediaType(
	 *             mediaType="application/json",
	 *             @OA\Schema(
	 * 							 @OA\Property(
	 * 									property="text",
	 * 									type="string",
	 * 									description="번역할 텍스트",
	 * 								),
	 *             ),
	 *         ),
	 *     ),
	 *     @OA\Response(response="200", description="Success"),
	 *     @OA\Response(response="400", description="Fail")
	 * )
	 */
	/**
	 * 번역 결과 받기
	 *
	 * 번역할 텍스트를 보내면 번역 결과를 받을 수 있습니다. ko->ja
	 */
	#[OpenApi\Operation(tags: ['Speech'], method: 'POST')]
	#[OpenApi\Parameters(factory: AccessTokenParameters::class)]
	#[OpenApi\RequestBody(factory: PronunciationTranslateResultRequestBody::class)]
	#[OpenApi\Response(factory: SuccessResponse::class, description: '번역 결과 받기 성공', statusCode: 200)]
	#[OpenApi\Response(factory: BadRequestResponse::class, description: '요청 오류', statusCode: 400)]
	#[OpenApi\Response(factory: UnauthorizedResponse::class, description: '인증 오류', statusCode: 401)]
	public function translate(Request $request)
	{
		$text = $request->input('text');
		$result = papagoTranslation('ko', 'ja', $text);
		return $result;
	}
}
