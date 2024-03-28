<?php

namespace App\Http\Controllers;

use App\Models\UserSetting;
use App\Models\ReviewNote;
use App\Models\VocabularyNote;
use App\Models\Ranking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WordOfWorldController extends Controller
{
  public function index(Request $request)
  {
    //
    $user = Auth::user();
    // 관리자 생성 문제 찾아서 같이 넘겨주기

    $notes = VocabularyNote::where('user_id', $user->id)->get();

    return response()->json(["status" => "Success", "notes" => $notes], 200);
  }


  /**
   * @OA\Post (
   *     path="/api/wordOfWorld",
   *     tags={"Game"},
   *     summary="게임 결과 저장",
   *     description="게임 결과 저장",
   *     @OA\Parameter(
   *         name="Authorization",
   *         in="header",
   *         required=true,
   *         description="액세스 토큰",
   *         example="Bearer access_token",
   *         @OA\Schema(type="string")
   *     ),
   *     @OA\RequestBody(
   *         description="단어장 정보",
   *         required=true,
   *         @OA\MediaType(
   *             mediaType="multipart/form-data",
   *             @OA\Schema(
   *                 @OA\Property(
   *                     property="score",
   *                     type="integer",
   *                     description="게임 점수",
   *                 ),
   *                @OA\Property(
   *                     property="kanji",
   *                     type="json",
   *                     description="오답 한자 리스트",
   *                 ),
   *                @OA\Property(
   *                     property="gana",
   *                     type="json",
   *                     description="오답 히라가나/카타카나 리스트",
   *                 ),
   *                @OA\Property(
   *                     property="meaning",
   *                     type="json",
   *                     description="오답 의미 리스트",
   *                 ),
   *             ),
   *         ),
   *     ),
   *     @OA\Response(response="200", description="Success"),
   *     @OA\Response(response="400", description="Fail")
   * )
   */
  public function result(Request $request)
  {
    $validator = Validator::make($request->json()->all(), [
      'score' => 'required|numeric|min:0',
      'gana' => 'required|json',
      'kanji' => 'required|json',
      'meaning' => 'required|json',
    ]);

    $user = Auth::user();

    $setting = $user->userSetting;
    if ($setting->review_note_auto_register) {
      $reviewNote = ReviewNote::firstOrNew(['user_id' => $user->id]);
      $reviewNote->kanji = array_merge($reviewNote->kanji, $request->kanji);
      $reviewNote->gana = array_merge($reviewNote->gana, $request->gana);
      $reviewNote->meaning = array_merge($reviewNote->meaning, $request->meaning);

      $result = duplicateCheck($reviewNote->kanji, $reviewNote->gana, $reviewNote->meaning);
      list($reviewNote->kanji, $reviewNote->gana, $reviewNote->meaning) = $result;
      $reviewNote->save();
    }


    if ($setting->score_auto_register) {
      $ranking = Ranking::firstOrNew(
        [
          'user_id' => $user->id,
          'level_id' => 6
        ],
        [
          'user_id' => $user->id,
          'level_id' => 6
        ]
      );
      if ($ranking->score < $request->score) {
        $ranking->score = $request->score;
        $ranking->save();
      }
    }

    $responseMessage = $reviewNote->wasRecentlyCreated ? "review note created" : "review note updated";
    return response()->json(["status" => "Success", "message" => $responseMessage], 200);
  }
}
