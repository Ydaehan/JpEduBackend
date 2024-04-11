<?php

namespace App\Http\Controllers;

use App\Models\VocabularyNote;
use App\Models\Score;
use App\OpenApi\RequestBodies\GameResultRequestBody;
use App\OpenApi\Responses\BadRequestResponse;
use App\OpenApi\Responses\ErrorValidationResponse;
use App\OpenApi\Responses\StoreSuccessResponse;
use App\OpenApi\Responses\SuccessResponse;
use App\OpenApi\Responses\UnauthorizedResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Vyuldashev\LaravelOpenApi\Attributes as OpenApi;

#[OpenApi\PathItem]
class GameController extends Controller
{
    /**
     * 게임 결과 저장
     *
     * 각 게임 결과를 저장합니다.<br/>
     * 게임 결과에 따라 오답노트를 생성하거나 갱신하고, 점수를 등록합니다.
     * 요청을 보낼 때 플레이한 난이도와 카테고리를 함께 보내야 합니다.
     */
    #[OpenApi\Operation(tags: ['Game'], method: 'POST')]
    #[OpenApi\RequestBody(factory: GameResultRequestBody::class)]
    #[OpenApi\Response(factory: StoreSuccessResponse::class, description: '생성/등록/수정 요청 성공', statusCode: 201)]
    #[OpenApi\Response(factory: BadRequestResponse::class, statusCode: 400, description: '요청 실패')]
    #[OpenApi\Response(factory: UnauthorizedResponse::class, statusCode: 401, description: '토큰 인증 실패')]
    #[OpenApi\Response(factory: ErrorValidationResponse::class, statusCode: 422, description: '유효성 검사 실패')]
    public function gameResult(Request $request)
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
                    ->where('level_id', 8)->first();
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
                $reviewNote->title = $user->nickname . "님의 오답노트";
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
