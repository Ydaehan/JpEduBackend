<?php

namespace App\Http\Controllers;

use App\Models\Score;
use App\OpenApi\Parameters\AccessTokenParameters;
use App\OpenApi\Parameters\GetCategoryRankingParameters;
use App\OpenApi\Responses\BadRequestResponse;
use App\OpenApi\Responses\SuccessResponse;
use App\OpenApi\Responses\UnauthorizedResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Vyuldashev\LaravelOpenApi\Attributes as OpenApi;

#[OpenApi\PathItem]
class RankingController extends Controller
{
    /**
     * 카테고리별 랭킹 조회
     *
     * 유저의 점수를 카테고리별로 랭킹을 조회합니다.<br/>
     * 카테고리는 JLPT, WorldOfWords, CardMatching이 있습니다.<br/>
     * 각 카테고리 내의 레벨별로 최고 점수를 조회할때는 level_id를 사용합니다.<br/>
     * 해당 데이터를 바탕으로 알맞게 가공하여 사용하시면 됩니다.
     */
    #[OpenApi\Operation(tags: ['Ranking'], method: 'GET')]
    #[OpenApi\Parameters(factory: GetCategoryRankingParameters::class)]
    #[OpenApi\Response(factory: SuccessResponse::class, description: '카테고리별 랭킹 조회 성공', statusCode: 200)]
    #[OpenApi\Response(factory: BadRequestResponse::class, description: '잘못된 요청', statusCode: 400)]
    #[OpenApi\Response(factory: UnauthorizedResponse::class, description: '토큰 인증 실패', statusCode: 401)]
    public function getCategoryRanking($category)
    {
        $rankings = Score::select('user_id', DB::raw('max(score) as max_score'), 'category', 'level_id')
            ->where('category', $category)
            ->groupBy('user_id', 'category', 'level_id')
            ->orderBy('max_score', 'desc')
            ->get();
        return response()->json($rankings);
    }

    /**
     * 내 모든 점수 이력 조회
     *
     * 로그인한 유저의 모든 점수 이력을 조회합니다.<br/>
     * 해당 데이터를 바탕으로 알맞게 가공하여 사용하시면 됩니다.
     */
    #[OpenApi\Operation(tags: ['Ranking'], method: 'GET')]
    #[OpenApi\Parameters(factory: AccessTokenParameters::class)]
    #[OpenApi\Response(factory: SuccessResponse::class, description: '내 모든 점수 이력 조회 성공', statusCode: 200)]
    #[OpenApi\Response(factory: BadRequestResponse::class, description: '잘못된 요청', statusCode: 400)]
    #[OpenApi\Response(factory: UnauthorizedResponse::class, description: '토큰 인증 실패', statusCode: 401)]
    public function getAllMyScore()
    {
        $userId = Auth::id();
        $scores = Score::where('user_id', $userId)->get();
        return response()->json($scores);
    }
}
