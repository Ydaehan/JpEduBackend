<?php

namespace App\Http\Controllers;

use App\Models\ReviewNote;
use App\Models\VocabularyNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
    $request->validate([
      'score' => 'required|numeric|min:0',
      'gana' => 'required|array',
      'kanji' => 'required|array',
      'meaning' => 'required|array',
    ]);
    // 
    $user = Auth::user();
    if (!$user) {
      return response()->json(['message' => 'Unauthorized'], 401);
    }

    $reviewNote = ReviewNote::where('user_id', $user->id)->first();
    if (!$reviewNote) {
      $reviewNote = new ReviewNote();
      $reviewNote->user_id = $user->id;
      $reviewNote->gana = $request->gana;
      $reviewNote->kanji = $request->kanji;
      $reviewNote->meaning = $request->meaning;
      $reviewNote->score = $request->score;
      $reviewNote->save();
      return response()->json(["status" => "Success", "message" => "review note created"], 200);
    }
    $reviewNoteGana = array_merge($reviewNote->gana, $request->gana);
    $reviewNoteKanji = array_merge($reviewNote->kanji, $request->kanji);
    $reviewNoteMeaning = array_merge($reviewNote->meaning, $request->meaning);



    $reviewNote->gana = $reviewNoteGana;
    $reviewNote->kanji = $reviewNoteKanji;
    $reviewNote->meaning = $reviewNoteMeaning;
    $reviewNote->score = $request->score;
    $reviewNote->save();
    return response()->json(["status" => "Success", "message" => "review note updated"], 200);
  }

  function removeDuplicates($kanjiArray, $meaningArray)
  {
    $uniqueKanji = array_unique($kanjiArray);

    foreach ($uniqueKanji as $kanji) {
      $kanjiIndexes = array_keys($kanjiArray, $kanji);
      $meanings = array_intersect_key($meaningArray, array_flip($kanjiIndexes));

      // 한자와 그에 해당하는 뜻이 모두 중복되는 경우 삭제
      if (count(array_unique($meanings)) === 1 && count($kanjiIndexes) > 1) {
        foreach ($kanjiIndexes as $index) {
          unset($meaningArray[$index]);
        }
        unset($kanjiArray[array_search($kanji, $kanjiArray)]);
      }
    }
  }
}
