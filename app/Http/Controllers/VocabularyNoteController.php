<?php

namespace App\Http\Controllers;

use App\Imports\VocabularyNoteImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Exception;

class VocabularyNoteController extends Controller
{
  //
  public function create(Request $request)
  {
    $request->validate([]);
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
      if (!$request->file('excel')) {
        return response()->json(["status" => "Fail", "message" => "VocabularyNoteController: no excel file"], 400);
      }
      $vocabularyNote = new VocabularyNoteImport();
      Excel::import($vocabularyNote, $request->file('excel'));

      return response()->json($vocabularyNote->getVocabularyNote(), 200);
    } catch (Exception $e) {
      return response()->json(["status" => "Error", "message" => "VocabularyNoteController: " . $e->getMessage()], 400);
    }
  }

  public function user(Request $request)
  {
  }
}
