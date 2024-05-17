<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SentenceNote;
use App\OpenApi\Parameters\AccessTokenParameters;
use App\OpenApi\Parameters\DeleteSentenceParameters;
use App\OpenApi\Parameters\SentenceUpdateParameters;
use App\OpenApi\RequestBodies\SentenceUpdateRequestBody;
use App\OpenApi\RequestBodies\StoreSentencesRequestBody;
use App\OpenApi\Responses\BadRequestResponse;
use App\OpenApi\Responses\ErrorValidationResponse;
use App\OpenApi\Responses\StoreSuccessResponse;
use App\OpenApi\Responses\SuccessResponse;
use App\OpenApi\Responses\UnauthorizedResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Vyuldashev\LaravelOpenApi\Attributes as OpenApi;

#[OpenApi\PathItem]
class TypingPracticeController extends Controller
{
	/**
	 * 관리자의 타자연습 파일 등록
	 *
	 * 관리자가 타자연습용 문장을 생성<br/>
	 * .txt 파일을 보내면 해당 파일을「 。」을 기준으로 줄바꿈하여 타자연습용 문장을 만듦.
	 */
	#[OpenApi\Operation(tags: ['AdminSentenceNote'], method: 'POST')]
	#[OpenApi\Parameters(factory: AccessTokenParameters::class)]
	#[OpenApi\RequestBody(factory: StoreSentencesRequestBody::class)]
	#[OpenApi\Response(factory: StoreSuccessResponse::class, description: '생성/등록/수정 요청 성공', statusCode: 201)]
	#[OpenApi\Response(factory: BadRequestResponse::class, description: '요청 실패', statusCode: 400)]
	#[OpenApi\Response(factory: UnauthorizedResponse::class, description: '인증 실패', statusCode: 401)]
	public function store(Request $request)
	{
		if ($request->hasFile('file')) {
			$file = $request->file('file');
			// 파일 열기
			$contents = file_get_contents($file->getRealPath());
			// 파일 읽기
			$replace_search = array("\n", "\r");
			$replace_target = array("", "");
			$contents = str_replace($replace_search, $replace_target, $contents);
			$contents = str_replace('。', "\n", $contents);
			// 줄바꿈 문자를 기준으로 분리하여 배열로 만듦
			$contents = rtrim($contents, "\n");
			$lines = explode("\n", $contents);
			// 의미 생성
			$source = "ja";
			$target = "ko";
			$meaning = papagoTranslation($source, $target, $contents);
			$translateResult = explode("\n", $meaning);
			$result = [];
			foreach ($lines as $index => $line) {
				$result[] = [
					'문장' => $line,
					'의미' => $translateResult[$index],
				];
			}

			foreach ($result as $index => $text) {
				$gooResult = Http::withHeaders([
					'Content-Type' => 'application/json',
				])->post('https://labs.goo.ne.jp/api/hiragana', [
					'app_id' => env('GOO_APP_ID'),
					'sentence' => $text["문장"],
					'output_type' => 'hiragana',
				])->json();
				$text['히라가나'] = $gooResult['converted'];
				$result[$index] = $text;
			}
			$encodedResult = json_encode($result);
			// 문장 데이터 생성
			$sentence = new SentenceNote();
			$sentence->user_id = 1;
			$sentence->title = "TypingExample";
			$sentence->sentences = $encodedResult;
			$sentence->situation = "AdminExample";
			$sentence->save();
		} else {
			echo "파일이 없습니다.";
		}
	}

	/**
	 * 관리자 문장 조회
	 *
	 * 관리자 문장을 조회합니다.<br/>
	 * 관리자가 생성한 모든 문장을 조회합니다.
	 */
	#[OpenApi\Operation(tags: ['AdminSentenceNote'], method: 'GET')]
	#[OpenApi\Parameters(factory: AccessTokenParameters::class)]
	#[OpenApi\Response(factory: SuccessResponse::class, description: '문장 조회 성공', statusCode: 200)]
	#[OpenApi\Response(factory: BadRequestResponse::class, description: '요청 실패', statusCode: 400)]
	#[OpenApi\Response(factory: UnauthorizedResponse::class, description: '인증 실패', statusCode: 401)]
	public function getSentences()
	{
		$sentences = SentenceNote::whereIn('user_id', [1, 2])->get();
		return $sentences;
	}

	/**
	 * 관리자의 타자연습용 문장 수정
	 *
	 * 관리자가 타자연습용 문장을 수정합니다.
	 */
	#[OpenApi\Operation(tags: ['AdminSentenceNote'], method: 'PATCH')]
	#[OpenApi\Parameters(factory: SentenceUpdateParameters::class)]
	#[OpenApi\RequestBody(factory: SentenceUpdateRequestBody::class)]
	#[OpenApi\Response(factory: StoreSuccessResponse::class, description: '생성/등록/수정 요청 성공', statusCode: 201)]
	#[OpenApi\Response(factory: BadRequestResponse::class, description: '요청 실패', statusCode: 400)]
	#[OpenApi\Response(factory: ErrorValidationResponse::class, description: '유효성 검사 실패', statusCode: 422)]
	public function update(string $id, Request $request)
	{
		$validator = Validator::make($request->all(), [
			'sentence' => 'required|string',
		]);
		if ($validator->fails()) {
			return response()->json($validator->errors(), 400);
		}

		$sentence = SentenceNote::find($id);

		if (!$sentence) {
			return response()->json(["message" => "존재하지 않는 문장입니다."], 400);
		}

		if ($sentence->sentence == $request->sentence) {
			return response()->json(["message" => "동일한 문장입니다."], 400);
		}

		$sentence->update(['sentence' => $request->sentence]);
		return response()->json(["message" => "success"]);
	}

	/**
	 * 관리자의 타자연습용 문장 노트 삭제
	 *
	 * 관리자가 타자연습용 문장 노트 을 삭제합니다.
	 */
	#[OpenApi\Operation(tags: ['AdminSentenceNote'], method: 'DELETE')]
	#[OpenApi\Parameters(factory: DeleteSentenceParameters::class)]
	#[OpenApi\Response(factory: SuccessResponse::class, description: '문장 노트 삭제 성공', statusCode: 200)]
	#[OpenApi\Response(factory: BadRequestResponse::class, description: '요청 실패', statusCode: 400)]
	#[OpenApi\Response(factory: UnauthorizedResponse::class, description: '인증 실패', statusCode: 401)]
	public function destroy(string $id)
	{
		$sentence = SentenceNote::find($id);
		if (!$sentence) {
			return response()->json(["message" => "존재하지 않는 노트입니다."], 400);
		}
		$sentence->delete();
		return response()->json(["message" => "success"]);
	}
}
