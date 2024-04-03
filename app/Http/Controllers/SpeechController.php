<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class SpeechController extends Controller
{
  //

  /**
   * @OA\Post (
   *     path="/api/speech",
   *     tags={"Speech"},
   *     summary="발음평과 결과 받기",
   *     description="자신의 발음 녹음본과 비교할 텍스트를 보내면 발음평가 결과를 받을 수 있습니다.",
   *     @OA\Parameter(
   *         name="Authorization",
   *         in="header",
   *         required=true,
   *         description="Bearer {access_token}",
   *         @OA\Schema(type="string")
   *     ),
   *     @OA\RequestBody(
   *         description="발음평가 요구 정보",
   *         required=true,
   *         @OA\MediaType(
   *             mediaType="multipart/form-data",
   *             @OA\Schema(
   *                 @OA\Property(
   *                     property="audio",
   *                     type="file",
   *                     description="녹음된 음성 파일",
   *                 ),
   *                 @OA\Property(
   *                      property="referenceText",
   *                      type="string",
   *                      description="비교할 텍스트",
   *                 ),
   *             ),
   *         ),
   *     ),
   *     @OA\Response(response="200", description="Success"),
   *     @OA\Response(response="400", description="Fail")
   * )
   */
  public function pronunciationAssessment(Request $request)
  {
    //
    try {
      $validator = Validator::make($request->all(), [
        'audio' => 'required|file|mimes:wav',
        'referenceText' => 'required|string',
      ]);

      $speechResponse = Http::asMultipart()->post('http://host.docker.internal:5000/speech', [
        [
          'name' => 'audio',
          'contents' => fopen($request->file('audio')->getRealPath(), 'r'),
          'filename' => $request->file('audio')->getClientOriginalName(),
        ],
        [
          'name' => 'referenceText',
          'contents' => $request->referenceText,
        ],
      ])->json();

      return response()->json([
        "status" => "Success",
        "speechResult" => $speechResponse,
        "message" => "발음 평가가 완료되었습니다."
      ]);
    } catch (Exception $e) {
      return response()->json([
        "status" => "Fail",
        "message" => "SpeechController: " . $e->getMessage()
      ], 400);
    }
  }
}
