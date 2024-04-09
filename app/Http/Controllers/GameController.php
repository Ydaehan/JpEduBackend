<?php

namespace App\Http\Controllers;

use App\Models\UserSetting;
use App\Models\ReviewNote;
use App\Models\VocabularyNote;
use App\Models\Score;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class GameController extends Controller
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
	 *     path="/api/worldOfWords",
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
	 *              required={"score", "kanji", "gana", "meaning", "level_id", "category"},
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
	 *                @OA\Property(
	 *                     property="level_id",
	 *                     type="integer",
	 *                     description="플레이한 게임 난이도",
	 *                     example="WorldOfWords"
	 *                 ),
	 *                @OA\Property(
	 *                     property="category",
	 *                     type="string",
	 *                     enum={"JLPT", "WorldOfWords", "CardMatching"},
	 *                     description="게임 카테고리",
	 *                     example="WorldOfWords"
	 *                 ),
	 *             ),
	 *         ),
	 *     ),
	 *     @OA\Response(response="200", description="Success"),
	 *     @OA\Response(response="400", description="Fail")
	 * )
	 */
	public function wordsResult(Request $request)
	{
		try {
			$validator = Validator::make($request->json()->all(), [
				'score' => 'required|numeric|min:0',
				'gana' => 'required|json',
				'kanji' => 'required|json',
				'meaning' => 'required|json',
				'category' => 'required|in:JLPT,WorldOfWords,CardMatching'
			]);

			$user = Auth::user();

			$setting = $user->userSetting;
			// 오답노트 생성, 갱신
			if ($setting->review_note_auto_register) {
				$reviewNote = VocabularyNote::where('user_id', $user->id)
					->where('level_id', 8)->firstOrFail();
				if ($reviewNote) {
					$reviewNote->kanji = array_merge($reviewNote->kanji, $request->kanji);
					$reviewNote->gana = array_merge($reviewNote->gana, $request->gana);
					$reviewNote->meaning = array_merge($reviewNote->meaning, $request->meaning);
					$result = duplicateCheck($reviewNote->kanji, $reviewNote->gana, $reviewNote->meaning);
					list($reviewNote->kanji, $reviewNote->gana, $reviewNote->meaning) = $result;
				} else {
					$reviewNote = new VocabularyNote();
					$reviewNote->kanji = $request->kanji;
					$reviewNote->gana = $request->gana;
					$reviewNote->meaning = $request->meaning;
				}
				$reviewNote->title = $user->nickname + "님의 오답노트";
				$reviewNote->user_id = $user->id;
				$reviewNote->level_id = 8;
				$reviewNote->save();
			}

			// 점수 등록이 활성화 되어있고, 점수가 0보다 크고, 게임 난이도가 6이하인 경우 랭킹 갱신
			if ($setting->score_auto_register && $request->score > 0 && $request->level_id <= 6) {
				$score = new Score();
				$score->user_id = $user->id;
				$score->score = $request->score;
				$score->category = $request->category;
				$score->level_id = $request->level_id;
				$score->save();
			}

			$responseMessage = $reviewNote->wasRecentlyCreated ? "review note created" : "review note updated";
			return response()->json(["status" => "Success", "message" => $responseMessage], 200);
		} catch (\Exception $e) {
			return response()->json(["status" => "Fail", "message" => "GameController" . $e->getMessage()], 400);
		}
	}
}
