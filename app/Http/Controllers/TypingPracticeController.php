<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sentence;

/**
 * @OA\Post (
 *     path="/api/open-file",
 *     tags={"TypingPractice"},
 *     summary="타자연습 파일 등록",
 *     description="타자연습용 문장 생성
 *     .txt 파일을 보내면 해당 파일을「 。」을 기준으로 줄바꿈하여 타자연습용 문장을 만듦.",
 *     @OA\RequestBody(
 *         description="txt 파일",
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 @OA\Property(
 *                     property="file",
 *                     type="file",
 *                 ),
 *             ),
 *         ),
 *     ),
 *     @OA\Response(response="200", description="Success"),
 *     @OA\Response(response="400", description="Fail")
 * )
 */
class TypingPracticeController extends Controller
{
  public function makeSentences(Request $request)
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
      $lines = explode("\n", $contents);
      // 배열의 각 원소를 순회하며 처리
      foreach ($lines as $line) {
        if ($line != '') {
          $existingSentence = Sentence::where('sentence', $line)->first();
          //똑같은 문장이면 들어가지 않게 처리
          if (!$existingSentence) {
            Sentence::create(['sentence' => $line]);
          }
        }
      }
    } else {
      echo "파일이 없습니다.";
    }
  }

  /**
   * @OA\Get (
   *     path="/api/typing/getSentences",
   *     tags={"TypingPractice"},
   *     summary="타자연습용 문장 조회",
   *     description="타자연습용 문장을 조회합니다.
   *     DB의 모든 문장을 들고옵니다.",
   *     @OA\Parameter(
   *         name="Authorization",
   *         in="header",
   *         required=true,
   *         description="Bearer access token",
   *         @OA\Schema(type="string")
   *     ),
   *     @OA\Response(response="200", description="Success"),
   *     @OA\Response(response="400", description="Fail")
   * )
   */
  public function getSentences()
  {
    $sentences = Sentence::all();
    return $sentences;
  }
}
