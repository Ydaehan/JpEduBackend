<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grammar;
use Illuminate\Support\Facades\Validator;

class GrammarController extends Controller
{

	/**
	 * @OA\Get (
	 *     path="/api/jlpt/grammar/{tier}",
	 *     tags={"JLPT Grammar"},
	 *     summary="JLPT 문법 등급별 조회",
	 *     description="JLPT 문법을 등급별 조회 합니다.",
	 *     @OA\Parameter(
	 *         name="Authorization",
	 *         in="header",
	 *         required=true,
	 *         description="Bearer {access_token}",
	 *         @OA\Schema(type="string")
	 *     ),
	 *     @OA\Parameter(
	 *        name="tier",
	 *        in="path",
	 *        required=true,
	 *        description="N1, N2, N3, N4, N5",
	 *        @OA\Schema(type="string")
	 *     ),
	 *     @OA\Response(response="200", description="Success"),
	 *     @OA\Response(response="400", description="Fail")
	 * )
	 */
	public function show($tier)
	{
		$validator = Validator::make(['tier' => $tier], [
			'tier' => 'required|in:N1,N2,N3,N4,N5'
		]);
		if ($validator->fails()) {
			return response()->json(['error' => $validator->errors()], 400);
		}
		$grammars = Grammar::where('tier', $tier)->get();

		// 'tier' 필드를 제외하고 반환
		$grammars = $grammars->map(function ($grammar) {
			unset($grammar->tier);
			return $grammar;
		});

		return response()->json($grammars);
	}

	/**
	 * @OA\Post (
	 *     path="/api/jlpt/grammar",
	 *     tags={"JLPT Grammar"},
	 *     summary="JLPT 문법 생성",
	 *     description="관리자가 문법을 생성 할 수 있습니다. (현재는 관리자가 미구현 상태라 필요없음 - json 파일로 한번에 생성)",
	 *     @OA\Parameter(
	 *         name="Authorization",
	 *         in="header",
	 *         required=true,
	 *         description="Bearer {관리자 access_token}",
	 *         @OA\Schema(type="string")
	 *     ),
	 *     @OA\RequestBody(
	 *         description="JLPT 등급별 문제 생성을 위한 json 파일",
	 *         required=true,
	 *         @OA\MediaType(
	 *             mediaType="multipart/form-data",
	 *             @OA\Schema(
	 * 							 type="object",
	 * 							 @OA\Property(
	 * 								 property="file",
	 * 								 type="file",
	 * 								 description="json 파일"
	 * 							 )
	 * 					 ),
	 * 			 ),
	 * 	 ),
	 * 	 @OA\Response(response="200", description="Success"),
	 * 	 @OA\Response(response="400", description="Fail")
	 * )
	 */
	public function create(Request $request)
	{
		// request에 json 타입의 파일을 받음
		try {
			$json = json_decode($request->file('file')->get(), true);
			// json 파일을 받아서 DB에 저장
			foreach ($json['data'] as $item) {
				$grammar = new Grammar();
				$grammar->grammar = $item['Grammar'];
				$grammar->explain = $item['Description'];
				// 5번 반복하면 $index 를 1씩 증가시키며 $item['Example' . $index]를 가져와서 json 형식으로 저장
				$example = [];
				for ($index = 1; $index <= 5; $index++) {
					if (isset($item['Example' . $index]) && $item['Example' . $index] != "") {
						$example[$index] = $item['Example' . $index];
					} else {
						$example[$index] = null;
					}
				}
				$grammar->example = json_encode($example);
				$grammar->mean = $item['Meaning'];
				$grammar->conjunction = $item['Connection'];
				$grammar->tier = $json['tier'];
				$grammar->save();
			}
		} catch (\Exception $e) {
			return response()->json(['error' => $e->getMessage()], 500);
		}
	}
}
