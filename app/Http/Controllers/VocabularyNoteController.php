<?php

namespace App\Http\Controllers;

use App\Imports\VocabularyNoteImport;
use App\Models\VocabularyNote;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class VocabularyNoteController extends Controller
{
  /**
   * @OA\Get (
   *     path="/api/vocabularyNote",
   *     tags={"VocabularyNote"},
   *     summary="단어장 리스트",
   *     description="단어장 리스트 리턴",
   *     @OA\Parameter(
   *         name="Authorization",
   *         in="header",
   *         required=true,
   *         description="Bearer {access_token}",
   *         @OA\Schema(type="string")
   *     ),
   *     @OA\Response(response="200", description="Success"),
   *     @OA\Response(response="400", description="Fail")
   * )
   */
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
   * @OA\Post (
   *     path="/api/vocabularyNote",
   *     tags={"VocabularyNote"},
   *     summary="단어장 생성",
   *     description="단어장 생성",
   *     @OA\Parameter(
   *         name="Authorization",
   *         in="header",
   *         required=true,
   *         description="Bearer {access_token}",
   *         @OA\Schema(type="string")
   *     ),
   *     @OA\RequestBody(
   *         description="단어장 정보",
   *         required=true,
   *         @OA\MediaType(
   *             mediaType="multipart/form-data",
   *             @OA\Schema(
   *                 @OA\Property(
   *                     property="title",
   *                     type="string",
   *                     description="단어장 이름",
   *                 ),
   *                @OA\Property(
   *                     property="kanji",
   *                     type="json",
   *                     description="한자 리스트",
   *                 ),
   *                @OA\Property(
   *                     property="gana",
   *                     type="json",
   *                     description="히라가나/카타카나 리스트",
   *                 ),
   *                @OA\Property(
   *                     property="meaning",
   *                     type="json",
   *                     description="의미 리스트",
   *                 ),
   *             ),
   *         ),
   *     ),
   *     @OA\Response(response="200", description="Success"),
   *     @OA\Response(response="400", description="Fail")
   * )
   */
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
      'kanji' => json_encode($kanji),
      'gana' => json_encode($gana),
      'meaning' => json_encode($meaning),
      'is_public' => $setting->vocabulary_note_auto_visibility,
      'is_create' => true
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
   * @OA\Get (
   *     path="/api/vocabularyNote/{id}",
   *     tags={"VocabularyNote"},
   *     summary="단어장 상세 정보",
   *     description="단어장 상세 정보",
   *      @OA\Parameter(
   *         name="id",
   *         in="path",
   *         required=true,
   *         description="단어장 노트의 id",
   *         @OA\Schema(type="string")
   *     ),
   *     @OA\Parameter(
   *         name="Authorization",
   *         in="header",
   *         required=true,
   *         description="Bearer {access_token}",
   *         @OA\Schema(type="string")
   *     ),
   *     @OA\Response(response="200", description="Success"),
   *     @OA\Response(response="400", description="Fail")
   * )
   */
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
   * @OA\Patch (
   *     path="/api/vocabularyNote/{id}",
   *     tags={"VocabularyNote"},
   *     summary="단어장 수정",
   *     description="단어장 수정",
   *     @OA\Parameter(
   *         name="id",
   *         in="path",
   *         required=true,
   *         description="단어장의 id",
   *         @OA\Schema(type="string")
   *     ),
   *     @OA\Parameter(
   *         name="Authorization",
   *         in="header",
   *         required=true,
   *         description="Bearer {access_token}",
   *         @OA\Schema(type="string")
   *     ),
   *     @OA\RequestBody(
   *         description="단어장 정보",
   *         required=true,
   *         @OA\MediaType(
   *             mediaType="multipart/form-data",
   *             @OA\Schema(
   *                 required={"title", "kanji", "gana", "meaning", "is_public"},
   *                 @OA\Property(
   *                     property="title",
   *                     type="string",
   *                     description="단어장 이름",
   *                 ),
   *                @OA\Property(
   *                     property="kanji",
   *                     type="json",
   *                     description="한자 리스트",
   *                 ),
   *                @OA\Property(
   *                     property="gana",
   *                     type="json",
   *                     description="히라가나/카타카나 리스트",
   *                 ),
   *                @OA\Property(
   *                     property="meaning",
   *                     type="json",
   *                     description="의미 리스트",
   *                 ),
   *                @OA\Property(
   *                      property="is_public",
   *                      type="boolean",
   *                      description="단어장 공유 여부",
   *                 ),
   *             ),
   *         ),
   *     ),
   *     @OA\Response(response="200", description="Success"),
   *     @OA\Response(response="400", description="Fail")
   * )
   */
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
   * @OA\Delete (
   *     path="/api/vocabularyNote/{id}",
   *     tags={"VocabularyNote"},
   *     summary="단어장 삭제",
   *     description="단어장 삭제",
   *     @OA\Parameter(
   *         name="id",
   *         in="path",
   *         required=true,
   *         description="단어장 노트의 id",
   *         @OA\Schema(type="string")
   *     ),
   *     @OA\Parameter(
   *         name="Authorization",
   *         in="header",
   *         required=true,
   *         description="Bearer {access_token}",
   *         @OA\Schema(type="string")
   *     ),
   *     @OA\Response(response="200", description="Success"),
   *     @OA\Response(response="400", description="Fail")
   * )
   */
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
   * @OA\Post (
   *     path="/api/vocabularyNote/export",
   *     tags={"VocabularyNote"},
   *     summary="Excel 단어장 생성",
   *     description="Excel 단어장 생성",
   *     @OA\Parameter(
   *         name="Authorization",
   *         in="header",
   *         required=true,
   *         description="Bearer {access_token}",
   *         @OA\Schema(type="string")
   *     ),
   *     @OA\RequestBody(
   *         description="단어장 정보",
   *         required=true,
   *         @OA\MediaType(
   *             mediaType="multipart/form-data",
   *             @OA\Schema(
   *                 @OA\Property(
   *                     property="excel",
   *                     type="file",
   *                     description="Excel 파일",
   *                 ),
   *             ),
   *         ),
   *     ),
   *     @OA\Response(response="200", description="Success"),
   *     @OA\Response(response="400", description="Fail")
   * )
   */
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

  // 로그인된 상태에서 사용가능하며, OCR -> 형태소분석 -> 명사,동사만 꺼내서 결과값 반환해주기
  /**
   * @OA\Post(
   *     path="/api/vocabularyNote/ocr",
   *     tags={"VocabularyNote"},
   *     summary="이미지를 텍스트로 변환 후 번역",
   *     description="이미지를 텍스트로 변환 후 번역 후 mecab을 이용해 동사와 명사만 추출하여 요미가나를 생성하여 보내줍니다.",
   *     @OA\Parameter(
   *         name="Authorization",
   *         in="header",
   *         required=true,
   *         description="Bearer access token",
   *         @OA\Schema(type="string")
   *     ),
   *     @OA\RequestBody(
   *         description="이미지",
   *         required=true,
   *         @OA\MediaType(
   *             mediaType="multipart/form-data",
   *             @OA\Schema(
   *                 @OA\Property(
   *                     property="image",
   *                     type="file",
   *                 ),
   *             ),
   *         ),
   *     ),
   *     @OA\Response(response="200", description="Success"),
   *     @OA\Response(response="400", description="Fail")
   * )
   * */
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
}
