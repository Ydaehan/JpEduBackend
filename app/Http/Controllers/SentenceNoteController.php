<?php

namespace App\Http\Controllers;

use App\Models\SentenceNote;
use App\OpenApi\Parameters\AccessTokenParameters;
use App\OpenApi\Parameters\TokenAndIdParameters;
use App\OpenApi\RequestBodies\StoreSentenceNotesRequestBody;
use App\OpenApi\RequestBodies\UpdateSentenceNoteRequestBody;
use App\OpenApi\Responses\BadRequestResponse;
use App\OpenApi\Responses\ErrorValidationResponse;
use App\OpenApi\Responses\StoreSuccessResponse;
use App\OpenApi\Responses\UnauthorizedResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Vyuldashev\LaravelOpenApi\Attributes as OpenApi;
use Illuminate\Support\Str;

#[OpenApi\PathItem]
class SentenceNoteController extends Controller
{
	/**
	 * 문장 노트 생성
	 * 
	 * 문장 노트를 생성합니다.
	 */
	#[OpenApi\Operation(tags: ['SentenceNote'], method: 'POST')]
	#[OpenApi\Parameters(factory: AccessTokenParameters::class)]
	#[OpenApi\RequestBody(factory: StoreSentenceNotesRequestBody::class)]
	#[OpenApi\Response(factory: StoreSuccessResponse::class, description: '생성/등록/수정 요청 성공', statusCode: 201)]
	#[OpenApi\Response(factory: BadRequestResponse::class, description: '요청 실패', statusCode: 400)]
	#[OpenApi\Response(factory: UnauthorizedResponse::class, description: '인증 실패', statusCode: 401)]
	#[OpenApi\Response(factory: ErrorValidationResponse::class, description: '유효성 검사 실패', statusCode: 422)]
	public function create(Request $request)
	{
		$validation = $request->validate([
			'title' => 'required|string',
			'sentences' => 'required|array',
			'situation' => 'required|string',
		]);
		$user = auth('sanctum')->user();
		$sentences = $validation['sentences'];

		foreach ($sentences as $index => $text) {
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

		$sentenceNote = new SentenceNote();
		$sentenceNote->user_id = $user->id;
		$sentenceNote->title = $validation['title'];
		$sentenceNote->sentences = json_encode($result);
		$sentenceNote->situation = $validation['situation'];
		$sentenceNote->save();
	}

	/** 
	 * 문장 노트 OCR
	 * 
	 * 이미지를 받아 문장 노트로 변환합니다.
	 */
	#[OpenApi\Operation(tags: ['SentenceNote'], method: 'Post')]
	public function imageOcr(Request $request)
	{
		$client_secret = config('services.naver_ocr.client_secret');
		$url = config('services.naver_ocr.url');
		$image_file = $request->file('image');

		$params = [
			'version' => 'V2',
			'requestId' => uniqid(),
			'timestamp' => time(),
			'images' => [
				[
					'format' => "jpg",
					'name' => "ocrResult",
					'lang' => 'ja',
				]
			]
		];

		$json = json_encode($params);

		$response = Http::withHeaders([
			'X-OCR-SECRET' => $client_secret
		])->attach('file', file_get_contents($image_file), 'image.jpg')->post($url, [
			'message' => $json
		]);

		$data = json_decode($response, true);
		$word = $data['images'][0]['fields'];

		$sentence = '';
		$sentences = [];
		$hiragana = [];
		$test = 0;
		foreach ($word as $index => $value) {
			// 한 문장씩 만들어 배열에 저장

			// . ! ? 중 하나를 만나면 문장을 종결하고 배열에 저장
			// 히라가나만 있는거 저장
			// boundingPoly 0~3 까지의 x,y 좌표로 요미가나의 크기를 구한 후 빼내기
			$boundingPoly = $value['boundingPoly']['vertices'];
			$height = $boundingPoly[2]['y'] - $boundingPoly[1]['y'];
			// echo $value['inferText'] . " : " . $height . "<br>";
			if ($height > 19) {
				$sentence = Str::of($sentence)->append($value['inferText']);
			}

			// $gooResult = Http::withHeaders([
			// 	'Content-Type' => 'application/json',
			// ])->post('https://labs.goo.ne.jp/api/hiragana', [
			// 	'app_id' => env('GOO_APP_ID'),
			// 	'sentence' => $value['inferText'],
			// 	'output_type' => 'hiragana',
			// ])->json();
		}

		if (preg_match('/[。！？]/u', $value['inferText'])) {
			// echo ($sentence . '<br>');
			$sentences[$index] = $sentence;
			$sentence = '';
		}
		dd($sentences);
	}

	/**
	 * 문장 노트 조회
	 * 
	 * 문장 노트를 조회합니다.
	 * 노트의 id를 받아서 조회합니다.
	 */
	#[OpenApi\Operation(tags: ['SentenceNote'], method: 'Get')]
	#[OpenApi\Parameters(factory: TokenAndIdParameters::class)]
	#[OpenApi\Response(factory: StoreSuccessResponse::class, description: '생성/등록/수정 요청 성공', statusCode: 201)]
	#[OpenApi\Response(factory: BadRequestResponse::class, description: '요청 실패', statusCode: 400)]
	#[OpenApi\Response(factory: UnauthorizedResponse::class, description: '인증 실패', statusCode: 401)]
	#[OpenApi\Response(factory: ErrorValidationResponse::class, description: '유효성 검사 실패', statusCode: 422)]
	public function getSentenceNote($id)
	{
		$sentenceNote = SentenceNote::find($id);
		$sentenceNote->sentences = json_decode($sentenceNote->sentences);
		return response()->json($sentenceNote);
	}

	/**
	 * 문장 노트 리스트
	 * 
	 * 문장 노트 리스트를 반환합니다.
	 * 유저 자신의 문장 노트와 관리자가 생성한 문장 노트를 반환합니다.
	 */
	#[OpenApi\Operation(tags: ['SentenceNote'], method: 'Get')]
	#[OpenApi\Parameters(factory: AccessTokenParameters::class)]
	#[OpenApi\Response(factory: StoreSuccessResponse::class, description: '생성/등록/수정 요청 성공', statusCode: 201)]
	#[OpenApi\Response(factory: BadRequestResponse::class, description: '요청 실패', statusCode: 400)]
	#[OpenApi\Response(factory: UnauthorizedResponse::class, description: '인증 실패', statusCode: 401)]
	#[OpenApi\Response(factory: ErrorValidationResponse::class, description: '유효성 검사 실패', statusCode: 422)]
	public function sentenceNoteLists()
	{
		$user = auth('sanctum')->user();
		$sentenceNotes = SentenceNote::where('user_id', $user->id)
			->orWhere('situation', 'AdminExample')
			->get()
			->map(function ($note) {
				$note->sentences = json_decode($note->sentences);
				return $note;
			});

		return response()->json($sentenceNotes);
	}

	/**
	 * 문장 노트 수정
	 * 
	 * 문장 노트를 수정합니다.
	 * 노트의 제목, 문장, 상황을 수정할 수 있습니다.
	 * 문장은 배열로 받아서 수정할 수 있습니다.
	 * 문장의 id도 필요합니다.
	 */
	#[OpenApi\Operation(tags: ['SentenceNote'], method: 'Patch')]
	#[OpenApi\Parameters(factory: AccessTokenParameters::class)]
	#[OpenApi\RequestBody(factory: UpdateSentenceNoteRequestBody::class)]
	#[OpenApi\Response(factory: StoreSuccessResponse::class, description: '생성/등록/수정 요청 성공', statusCode: 201)]
	#[OpenApi\Response(factory: BadRequestResponse::class, description: '요청 실패', statusCode: 400)]
	#[OpenApi\Response(factory: UnauthorizedResponse::class, description: '인증 실패', statusCode: 401)]
	#[OpenApi\Response(factory: ErrorValidationResponse::class, description: '유효성 검사 실패', statusCode: 422)]
	public function update(Request $request)
	{
		$validation = $request->validate([
			'id' => 'required|integer',
			'title' => 'string',
			'sentences' => 'array',
			'situation' => 'string',
		]);
		try {
			if (isset($validation['title']) || isset($validation['sentences']) || isset($validation['situation'])) {
				$user = auth('sanctum')->user();
				$sentenceNote = SentenceNote::find($validation['id']);
				if (isset($validation['title'])) {
					$sentenceNote->title = $validation['title'];
				}
				if (isset($validation['sentences'])) {
					$sentences = $validation['sentences'];
					foreach ($sentences as $index => $text) {
						$gooResult = Http::withHeaders([
							'Content-Type' => 'application/json',
						])->post('https://labs.goo.ne.jp/api/hiragana', [
							'app_id' => env('GOO_APP_ID'),
							'sentence' => $text["문장"],
							'output_type' => 'hiragana',
						])->json();
						$text['히라가나'] = $gooResult['converted'];
						$sentences[$index] = $text;
					}
					$sentenceNote->sentences = json_encode($sentences);
				}
				if (isset($validation['situation'])) {
					$sentenceNote->situation = $validation['situation'];
				}
				$sentenceNote->user_id = $user->id;
				$sentenceNote->save();
			}
		} catch (\Exception $e) {
			return response()->json(['message' => '문장 노트 수정에 실패했습니다.'], 400);
		}
	}

	/**
	 * 문장 노트 삭제
	 * 
	 * 문장 노트를 삭제합니다.
	 * 노트의 id를 받아서 삭제합니다.
	 */
	#[OpenApi\Operation(tags: ['SentenceNote'], method: 'Delete')]
	#[OpenApi\Parameters(factory: TokenAndIdParameters::class)]
	#[OpenApi\Response(factory: StoreSuccessResponse::class, description: '생성/등록/수정 요청 성공', statusCode: 201)]
	#[OpenApi\Response(factory: BadRequestResponse::class, description: '요청 실패', statusCode: 400)]
	#[OpenApi\Response(factory: UnauthorizedResponse::class, description: '인증 실패', statusCode: 401)]
	#[OpenApi\Response(factory: ErrorValidationResponse::class, description: '유효성 검사 실패', statusCode: 422)]
	public function destroy($id)
	{
		try {
			$sentenceNote = SentenceNote::find($id);
			if ($sentenceNote->situation !== 'AdminExample') {
				$sentenceNote->delete();
				return response()->json(['message' => '문장 노트 삭제에 성공했습니다.'], 200);
			} else {
				return response()->json(['message' => '관리자 문장은 삭제할 수 없습니다.'], 400);
			}
		} catch (\Exception $e) {
			return response()->json(['message' => $e->getMessage()]);
		}
	}
}
