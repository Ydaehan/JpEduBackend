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
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    //
    $user = Auth::user();
    if (!$user) {
      return response()->json(['message' => 'Unauthorized'], 401);
    }

    // 관리자 생성 문제 찾아서 같이 넘겨주기

    $notes = VocabularyNote::where('user_id', $user->id)->get();

    return response()->json(["status" => "Success", "data" => $notes], 200);
  }

  public function result(Request $request)
  {
    $validator = Validator::make($request->json()->all(), [
      'score' => 'required|numeric|min:0',
      'gana' => 'required|array',
      'kanji' => 'required|array',
      'meaning' => 'required|array',
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
