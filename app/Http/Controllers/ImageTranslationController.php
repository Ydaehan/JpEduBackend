<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Utils;
use GuzzleHttp\Psr7\MultipartStream;
use Youaoi\MeCab\MeCab;


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
        $client_secret = env('APP_PAPAGO_API_CLIENT_SECRET_KEY'); // Replace with your actual client secret
        $client_id = env('APP_PAPAGO_APIGW_CLIENT_ID'); // Replace with your actual client ID

        $uploaded_file = $request->file('image');

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
                'contents' => Utils::streamFor(fopen($uploaded_file->path(), 'r')),
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

        // sourceText와 targetText를 배열로 변환
        $sourceTextArray = explode("\n", $responseContent['data']['sourceText']);
        $targetTextArray = explode("\n", $responseContent['data']['targetText']);

        // 일본어가 아닌 요소를 삭제하고 해당 인덱스에 맞게 TargetText에서도 삭제
        $filteredSourceTextArray = [];
        $filteredTargetTextArray = [];

        foreach ($sourceTextArray as $index => $sourceLine) {
        // 일본어인지 확인하고 한자 부분에 히라가나만 있거나 한글이 인식되었을 때 해당 인덱스를 기준으로 삭제
        // 해당 일본어에 한자가 포함되어 있으면 지우지 않기
            if (preg_match_all('!['
            .'\x{2E80}-\x{2EFF}'// 한,중,일 부수 보충
            .'\x{31C0}-\x{31EF}\x{3200}-\x{32FF}'
            .'\x{3400}-\x{4DBF}\x{4E00}-\x{9FBF}\x{F900}-\x{FAFF}'
            .'\x{20000}-\x{2A6DF}\x{2F800}-\x{2FA1F}'// 한,중,일 호환한자
            .']+!u', $sourceLine, $match)) {
                $filteredSourceTextArray[] = $sourceLine;
                $filteredTargetTextArray[] = $targetTextArray[$index];
            }
        }


        $uniqueSourceArray = array_unique($filteredSourceTextArray);
        $uniqueTargetArray = $filteredTargetTextArray;

        foreach ($sourceTextArray as $key => $value) {
            if(!array_key_exists($key, $uniqueSourceArray)){
                unset($uniqueTargetArray[$key]);
            }
        }
        // uniqueSourceArray의 요소들을 형태소분석하여 동사와 명사만 추출 하여 읽는법 까지 3가지로 반환해주기
        $mecab = new Mecab();
        $result = [];
        // $index 정렬
        $uniqueSourceArray = array_values($uniqueSourceArray);
        $uniqueTargetArray = array_values($uniqueTargetArray);
        foreach ($uniqueSourceArray as $index => $item) {
            $reading = $mecab->parse($item);
            if($reading[0]->speech === '動詞' || $reading[0]->speech === '名詞'){
                $result[$index] = $reading[0]->reading;
            }
            else{
                unset($uniqueSourceArray[$index]);
                unset($uniqueTargetArray[$index]);
            }
        }
        // index 정렬
        $uniqueSourceArray = array_values($uniqueSourceArray);
        $uniqueTargetArray = array_values($uniqueTargetArray);
        $result = array_values($result);

        // 일어와 번역된 한국어를 반환
        return response()->json(['kanji' => $uniqueSourceArray, 'gana' => $result,'meaning' => $uniqueTargetArray]);
    }
}
