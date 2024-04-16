<?php

namespace App\Http\Controllers;

use App\Imports\VocabularyNoteImport;
use App\Models\VocabularyNote;
use App\OpenApi\Parameters\AccessTokenParameters;
use App\OpenApi\Parameters\TokenAndIdParameters;
use App\OpenApi\RequestBodies\ImageRequestBody;
use App\OpenApi\RequestBodies\StoreExcelVocaRequestBody;
use App\OpenApi\RequestBodies\StoreVocabularyNotesRequestBody;
use App\OpenApi\RequestBodies\UpdateVocabularyNotesRequestBody;
use App\OpenApi\Responses\BadRequestResponse;
use App\OpenApi\Responses\ErrorValidationResponse;
use App\OpenApi\Responses\StoreSuccessResponse;
use App\OpenApi\Responses\SuccessResponse;
use App\OpenApi\Responses\UnauthorizedResponse;
use App\OpenApi\Schemas\StoreExcelVocaSchema;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Exception;
use GrahamCampbell\ResultType\Success;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Vyuldashev\LaravelOpenApi\Attributes as OpenApi;

#[OpenApi\PathItem]
class VocabularyNoteController extends Controller
{
  /**
   * 단어장 리스트
   *
   * 단어장 리스트를 반환합니다.
   */
  #[OpenApi\Operation(tags: ['VocabularyNote'], method: 'GET')]
  #[OpenApi\Parameters(factory: AccessTokenParameters::class)]
  #[OpenApi\Response(factory: SuccessResponse::class, description: '단어장 조회 성공', statusCode: 200)]
  #[OpenApi\Response(factory: BadRequestResponse::class, description: '단어장 조회 실패', statusCode: 400)]
  #[OpenApi\Response(factory: UnauthorizedResponse::class, description: '인증 실패', statusCode: 401)]
  public function index()
  {
    /** @var \App\Models\User $user **/
    $user = auth('sanctum')->user();

    $notes = $user->vocabularyNotes()->get();

    // 관리자 생성 문제 찾아서 같이 넘겨주기

    return response()->json([
      "status" =>   "Success",
      "notes" => $notes
    ], 200);
  }

  /**
   * 단어장 생성
   *
   * 단어장을 생성합니다.
   */
  #[OpenApi\Operation(tags: ['VocabularyNote'], method: 'POST')]
  #[OpenApi\Parameters(factory: AccessTokenParameters::class)]
  #[OpenApi\RequestBody(factory: StoreVocabularyNotesRequestBody::class)]
  #[OpenApi\Response(factory: StoreSuccessResponse::class, description: '단어장 생성 성공', statusCode: 201)]
  #[OpenApi\Response(factory: BadRequestResponse::class, description: '요청 실패', statusCode: 400)]
  #[OpenApi\Response(factory: UnauthorizedResponse::class, description: '인증 실패', statusCode: 401)]
  #[OpenApi\Response(factory: ErrorValidationResponse::class, description: '유효성 검사 실패', statusCode: 422)]
  public function store(Request $request)
  {
    $validator = Validator::make($request->json()->all(), [
      'title' => 'required|string|max:255',
      'kanji' => 'required|json',
      'gana' => 'required|json',
      'meaning' => 'required|json',
    ]);
    /** @var \App\Models\User $user **/
    $user = auth('sanctum')->user();

    $duplicateResult = duplicateCheck($request->kanji, $request->gana, $request->meaning);
    list($kanji, $gana, $meaning) = $duplicateResult;

    $setting = $user->userSetting;

    $note = VocabularyNote::create([
      'title' => $request->title,
      'user_id' => $user->id,
      'level_id' => $request->level_id ?? 7,
      'kanji' => json_encode($kanji),
      'gana' => json_encode($gana),
      'meaning' => json_encode($meaning),
      'is_public' => $setting->vocabulary_note_auto_visibility,
      'is_creator' => true
    ]);

    return response()->json(
      [
        "message" => "VocabularyNote created successfully",
        "note" => $note
      ],
      200
    );
  }

  /**
   * 단어장 상세 정보
   *
   * 단어장 상세 정보를 조회합니다.
   */
  #[OpenApi\Operation(tags: ['VocabularyNote'], method: 'GET')]
  #[OpenApi\Parameters(factory: TokenAndIdParameters::class)]
  #[OpenApi\Response(factory: SuccessResponse::class, description: '단어장 상세정보 조회 성공', statusCode: 200)]
  #[OpenApi\Response(factory: BadRequestResponse::class, description: '요청 실패', statusCode: 400)]
  #[OpenApi\Response(factory: UnauthorizedResponse::class, description: '인증 실패', statusCode: 401)]
  public function show(string $id)
  {
    /** @var \App\Models\User $user **/
    $user = auth('sanctum')->user();

    $note = $user->vocabularyNotes()->where('id', $id)->first();

    if ($note) {
      return response()->json(["status" => "Success", "note" => $note], 200);
    }
    return response()->json(["status" => "Fail", "message" => "VocabularyNoteController: Not Found VocabularyNote"], 400);
  }

  /**
   * 단어장 수정
   *
   * 단어장을 수정합니다.
   */
  #[OpenApi\Operation(tags: ['VocabularyNote'], method: 'PATCH')]
  #[OpenApi\Parameters(factory: TokenAndIdParameters::class)]
  #[OpenApi\RequestBody(factory: UpdateVocabularyNotesRequestBody::class)]
  #[OpenApi\Response(factory: SuccessResponse::class, description: '단어장 수정 성공', statusCode: 200)]
  #[OpenApi\Response(factory: BadRequestResponse::class, description: '요청 실패', statusCode: 400)]
  #[OpenApi\Response(factory: UnauthorizedResponse::class, description: '인증 실패', statusCode: 401)]
  #[OpenApi\Response(factory: ErrorValidationResponse::class, description: '유효성 검사 실패', statusCode: 422)]
  public function update(Request $request, string $id)
  {
    try {
      $validator = Validator::make($request->json()->all(), [
        'title' => 'required|string|max:255',
        'kanji' => 'required|json',
        'gana' => 'required|json',
        'meaning' => 'required|json',
        'is_public' => 'required|boolean'
      ]);

      /** @var \App\Models\User $user **/
      $user = auth('sanctum')->user();

      /** @var \App\Models\VocabularyNote $note **/
      $note = $user->vocabularyNotes()->where('id', $id)->first();

      if (!$note) {
        return response()->json(["status" => "Fail", "message" => "VocabularyNoteController: Not Found VocabularyNote"], 400);
      }

      list($kanji, $gana, $meaning) = duplicateCheck($request->kanji, $request->gana, $request->meaning);

      $note->update([
        'title' => $request->title,
        'kanji' => json_encode($kanji),
        'gana' => json_encode($gana),
        'meaning' => json_encode($meaning),
        'is_public' => $request->is_public,
      ]);

      return response()->json([
        "status" => "Success",
        "message" => "VocabularyNoteController: Updated VocabularyNote",
        "note" => $note
      ], 200);
    } catch (Exception $e) {
      return response()->json(["status" => "Fail", "message" => "VocabularyNoteController: " . $e->getMessage()], 400);
    }
  }

  /**
   * 단어장 삭제
   *
   * 단어장을 삭제합니다.
   */
  #[OpenApi\Operation(tags: ['VocabularyNote'], method: 'DELETE')]
  #[OpenApi\Parameters(factory: TokenAndIdParameters::class)]
  #[OpenApi\Response(factory: SuccessResponse::class, description: '단어장 삭제 성공', statusCode: 200)]
  #[OpenApi\Response(factory: BadRequestResponse::class, description: '요청 실패', statusCode: 400)]
  #[OpenApi\Response(factory: UnauthorizedResponse::class, description: '인증 실패', statusCode: 401)]
  #[OpenApi\Response(factory: ErrorValidationResponse::class, description: '유효성 검사 실패', statusCode: 422)]
  public function destroy($id)
  {
    try {
      /** @var \App\Models\User $user **/
      $user = auth('sanctum')->user();

      $user->vocabularyNotes()->where('id', $id)->delete();
      return response()->json(["status" => "Success", "message" => "VocabularyNoteController: VocabularyNote Deleted"], 200);
    } catch (Exception $e) {
      return response()->json(["status" => "Fail", "message" => "VocabularyNoteController: " . $e->getMessage()], 400);
    }
  }

  /**
   * Excel 단어장 생성
   *
   * Excel 파일로 단어장을 생성합니다.
   */
  #[OpenApi\Operation(tags: ['VocabularyNote'], method: 'POST')]
  #[OpenApi\Parameters(factory: AccessTokenParameters::class)]
  #[OpenApi\RequestBody(factory: StoreExcelVocaRequestBody::class)]
  #[OpenApi\Response(factory: StoreSuccessResponse::class, description: 'Excel 단어장 생성 성공', statusCode: 201)]
  #[OpenApi\Response(factory: BadRequestResponse::class, description: '요청 실패', statusCode: 400)]
  #[OpenApi\Response(factory: UnauthorizedResponse::class, description: '인증 실패', statusCode: 401)]
  #[OpenApi\Response(factory: ErrorValidationResponse::class, description: '유효성 검사 실패', statusCode: 422)]
  public function export(Request $request)
  {
    try {
      $validator = Validator::make($request->json()->all(), [
        'excel' => 'required|file',
      ]);

      $vocabularyNote = new VocabularyNoteImport();
      Excel::import($vocabularyNote, $request->file('excel'));
      return response()->json([
        "status" => "Success",
        "message" => "VocabularyNoteController: Excel VocabularyNote",
        "note" => $vocabularyNote->getVocabularyNote()
      ], 200);
    } catch (Exception $e) {
      return response()->json(["status" => "Error", "message" => "VocabularyNoteController: " . $e->getMessage()], 400);
    }
  }

  /**
   * 이미지를 텍스트로 변환 후 번역
   *
   * 이미지를 텍스트로 변환 후 번역 후 mecab을 이용해 동사와 명사만 추출하여 요미가나를 생성하여 보내줍니다.
   */
  #[OpenApi\Operation(tags: ['VocabularyNote'], method: 'POST')]
  #[OpenApi\Parameters(factory: AccessTokenParameters::class)]
  #[OpenApi\RequestBody(factory: ImageRequestBody::class)]
  #[OpenApi\Response(factory: SuccessResponse::class, description: '이미지 OCR 번역 성공', statusCode: 200)]
  #[OpenApi\Response(factory: BadRequestResponse::class, description: '요청 실패', statusCode: 400)]
  #[OpenApi\Response(factory: UnauthorizedResponse::class, description: '인증 실패', statusCode: 401)]
  public function textOcr(Request $request)
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
    // JSON 인코딩
    $json = json_encode($params);

    $response = Http::withHeaders([
      'X-OCR-SECRET' => $client_secret
    ])->attach('file', file_get_contents($image_file), 'image.jpg')->post($url, [
      'message' => $json
    ]);

    $status_code = $response->status();
    $data = json_decode($response, true);
    $inferTexts = array();
    foreach ($data['images'] as $image) {
      foreach ($image['fields'] as $field) {
        if ($field['inferConfidence'] > 0.8) {
          $inferTexts[] = $field['inferText'];
        }
      }
    }

    // 인식된 문자열에서 일본어만 들고옴
    $kanji = getKanji($inferTexts);
    $mecabResult = Http::withHeaders([
      'Content-Type' => 'application/json',
    ])->post('http://host.docker.internal:5000/mecab', [
      'texts' => $kanji
    ])->json();
    // 파파고 번역
    $text = implode("\n", $mecabResult['source']);
    $source = "ja";
    $target = "ko";
    $meaning = papagoTranslation($source, $target, $text);
    $translateResult = explode("\n", $meaning);
    // 히라가나, 가타카나 필터링 -> null
    $filteredKanji = kanjiFilter($mecabResult['source']);
    // 중복 체크
    $result = duplicateCheck($filteredKanji, $mecabResult['gana'], $translateResult);

    if ($status_code == 200) {
      return response()->json(['kanji' => array_values($result[0]), 'gana' => array_values($result[1]), 'meaning' => array_values($result[2]), 200]);
    } else {
      return response()->json(["status" => "Error", "message" => "VocabularyNoteController: " . $data['errorMessage']], 400);
    }
  }

  /**
   * public 단어장 리스트
   *
   * 공개여부가 public인 단어장 리스트 리턴
   */
  #[OpenApi\Operation(tags: ['VocabularyNote'], method: 'GET')]
  #[OpenApi\Parameters(factory: AccessTokenParameters::class)]
  #[OpenApi\Response(factory: SuccessResponse::class, description: '공개 단어장 조회 성공', statusCode: 200)]
  #[OpenApi\Response(factory: BadRequestResponse::class, description: '요청 실패', statusCode: 400)]
  #[OpenApi\Response(factory: UnauthorizedResponse::class, description: '인증 실패', statusCode: 401)]
  public function publicIndex()
  {
    $notes = VocabularyNote::where('is_public', true)->get();
    return response()->json([
      "status" => "Success",
      "notes" => $notes
    ], 200);
  }
}
