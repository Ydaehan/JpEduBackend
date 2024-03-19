<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Utils;
use GuzzleHttp\Psr7\MultipartStream;


class ImageTranslationController extends Controller
{
  // 로그인된 상태에서 사용가능하며, OCR -> 형태소분석 -> 명사,동사만 꺼내서 결과값 반환해주기
  /**
   * @OA\Post(
   *     path="/api/ocr",
   *     tags={"ImageOCR"},
   *     summary="이미지를 텍스트로 변환 후 번역",
   *     description="이미지를 텍스트로 변환 후 번역 후 mecab을 이용해 동사와 명사만 추출하여 요미가나를 생성하여 보내줍니다.",
   *     @OA\Parameter(
   *         name="Authorization",
   *         in="header",
   *         required=true,
   *         description="Bearer access token",
   *         @OA\Schema(type="string")
   *     ),
   *     @OA\RequestBody(
   *         description="이미지",
   *         required=true,
   *         @OA\MediaType(
   *             mediaType="multipart/form-data",
   *             @OA\Schema(
   *                 @OA\Property(
   *                     property="image",
   *                     type="file",
   *                 ),
   *             ),
   *         ),
   *     ),
   *     @OA\Response(response="200", description="Success"),
   *     @OA\Response(response="400", description="Fail")
   * )
   * */
  public function translateImage(Request $request)
  {
    $source_lang = $request->input('source', 'ja');
    $target_lang = $request->input('target', 'ko');
    $client_secret = env('APP_PAPAGO_API_CLIENT_SECRET_KEY');
    $client_id = env('APP_PAPAGO_APIGW_CLIENT_ID');
    $uploaded_file = $request->file('image');

    if (!$uploaded_file) {
      return response()->json(['message' => 'File not found'], 400);
    }

    $filePath = setImageSize($uploaded_file);

    // Create a MultipartStream for the data
    $multipartStream = new MultipartStream([
      [
        'name' => 'source',
        'contents' => $source_lang,
      ],
      [
        'name' => 'target',
        'contents' => $target_lang,
      ],
      [
        'name' => 'image',
        'contents' => Utils::streamFor(fopen($filePath, 'r')),
        'filename' => $uploaded_file->getClientOriginalName(),
      ]
    ]);

    $client = new Client();

    $url = "https://naveropenapi.apigw.ntruss.com/image-to-text/v1/translate";

    $response = $client->request('POST', $url, [
      'headers' => [
        'Content-Type' => 'multipart/form-data; boundary=' . $multipartStream->getBoundary(),
        'X-NCP-APIGW-API-KEY-ID' => $client_id,
        'X-NCP-APIGW-API-KEY' => $client_secret,
      ],
      'body' => $multipartStream
    ]);

    // Return the response from the Naver API
    $responseContent = json_decode($response->getBody()->getContents(), true);
    // dd($responseContent);

    // sourceText와 targetText를 배열로 변환
    $sourceTextArray = explode("\n", $responseContent['data']['sourceText']);
    $targetTextArray = explode("\n", $responseContent['data']['targetText']);

    $getResult = getKanji($sourceTextArray, $targetTextArray);
    $uniqueSourceArray = array_unique($getResult[0]);
    $uniqueTargetArray = $getResult[1];

    foreach ($sourceTextArray as $key => $value) {
      if (!array_key_exists($key, $uniqueSourceArray)) {
        unset($uniqueTargetArray[$key]);
      }
    }

    // $index 번호 제거
    $uniqueSourceArray = array_values($uniqueSourceArray);
    $uniqueTargetArray = array_values($uniqueTargetArray);

    $mecabResult = getMecab($uniqueSourceArray, $uniqueTargetArray);

    // 일어와 번역된 한국어를 반환
    return response()->json(['kanji' => $mecabResult[0], 'gana' => $mecabResult[1], 'meaning' => $mecabResult[2]]);
  }
  // ocr , papago 분리
  // 단어장 생성의 정확도 향상
}
