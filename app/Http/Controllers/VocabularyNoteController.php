<?php

namespace App\Http\Controllers;

use App\Imports\VocabularyNoteImport;
use App\Models\VocabularyNote;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class VocabularyNoteController extends Controller
{
  /**
   * @OA\Get (
   *     path="/api/vocabularyNote/index",
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

    return response()->json(["status" =>   "Success", "data" => $notes], 200);
  }

  /**
   * @OA\Post (
   *     path="/api/vocabularyNote/store",
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
        'message' => 'VocabularyNote created successfully',
        'data' => $note
      ],
      200
    );
  }

  /**
   * @OA\Get (
   *     path="/api/vocabularyNote/show/{id}",
   *     tags={"VocabularyNote"},
   *     summary="단어장 리스트",
   *     description="단어장 리스트 리턴",
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
      return response()->json(["status" => "Success", "data" => $note], 200);
    }
    return response()->json(["status" => "Fail", "message" => "VocabularyNoteController: Not Found VocabularyNote"], 400);
  }


  /**
   * @OA\Patch (
   *     path="/api/vocabularyNote/update/{id}",
   *     tags={"VocabularyNote"},
   *     summary="단어장 수정",
   *     description="단어장 수정",
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
   *                @OA\Property(
   *                      property="is_public",
   *                       type="boolean",
   *                       description="단어장 공유 여부",
   *                 ), 
   *             ),
   *         ),
   *     ),
   *     @OA\Response(response="200", description="Success"),
   *     @OA\Response(response="400", description="Fail")
   * )
   */
  public function update(Request $request)
  {
    try {
      $validator = Validator::make($request->json()->all(), [
        'title' => 'required|string|max:255',
        'kanji' => 'required|json',
        'gana' => 'required|json',
        'meaning' => 'required|json',
        'is_public' => 'boolean'
      ]);

      /** @var \App\Models\User $user **/
      $user = auth('sanctum')->user();

      $note = $user->vocabularyNotes()->where('id', $request->id)->first();

      $duplicateResult = duplicateCheck($request->kanji, $request->gana, $request->meaning);
      list($kanji, $gana, $meaning) = $duplicateResult;
      if ($note) {
        $note->title = $request->title;
        $note->user_id = $user->id;
        $note->kanji = $kanji;
        $note->gana = $gana;
        $note->meaning = $meaning;
        $note->is_public = $request->is_public;
        $note->save();
        return response()->json([
          "status" => "Success",
          "message" => "VocabularyNoteController: Updated VocabularyNote",
          "data" => $note
        ], 200);
      }
      return response()->json(["status" => "Fail", "message" => "VocabularyNoteController: Not Found VocabularyNote"], 400);
    } catch (Exception $e) {
      return response()->json(["status" => "Fail", "message" => "VocabularyNoteController: " . $e->getMessage()], 400);
    }
  }


  /**
   * @OA\Delete (
   *     path="/api/vocabularyNote/delete/{id}",
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
      return response()->json($vocabularyNote->getVocabularyNote(), 200);
    } catch (Exception $e) {
      return response()->json(["status" => "Error", "message" => "VocabularyNoteController: " . $e->getMessage()], 400);
    }
  }
}
