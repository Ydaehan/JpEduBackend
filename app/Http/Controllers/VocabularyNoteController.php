<?php

namespace App\Http\Controllers;

use App\Imports\VocabularyNoteImport;
use App\Models\VocabularyNote;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Exception;
use Illuminate\Support\Facades\Auth;

class VocabularyNoteController extends Controller
{
  //

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
      // $request->validator([
      //   'excel' => 'required',
      // ]);


      $user = Auth::user();
      if (!$user) {
        return response()->json(['message' => 'Unauthorized'], 401);
      }

      $vocabularyNote = new VocabularyNoteImport();
      Excel::import($vocabularyNote, $request->file('excel'));

      return response()->json($vocabularyNote->getVocabularyNote(), 200);
    } catch (Exception $e) {
      return response()->json(["status" => "Error", "message" => "VocabularyNoteController: " . $e->getMessage()], 400);
    }
  }

  public function userCreate(Request $request)
  {
    $request->validate([
      'title' => 'required|string|max:255',
      'kanji' => 'required|array',
      'gana' => 'required|array',
      'meaning' => 'required|array',
    ]);
    $user = Auth::user();
    if (!$user) {
      return response()->json(['message' => 'Unauthorized'], 401);
    }

    $duplicateResult = duplicateCheck($request->kanji, $request->gana, $request->meaning);
    list($kanji, $gana, $meaning) = $duplicateResult;
    $setting = UserSetting::find('user_id', $user->id);
    $note = VocabularyNote::create([
      'title' => $request->title,
      'kanji' => json_encode($kanji),
      'gana' => json_encode($gana),
      'meaning' => json_encode($meaning),
      'is_public' => $setting->is_public, // 임시 하드 코딩, 세팅에서 들고 올 것
    ]);

    return response()->json(
      [
        'message' => 'VocabularyNote created successfully',
        'note' => $note
      ],
      200
    );
  }
}
