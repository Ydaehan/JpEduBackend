<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Utils;
use GuzzleHttp\Psr7\MultipartStream;


class ImageTranslationController extends Controller
{
    // 로그인된 상태에서 사용가능하며, OCR -> 형태소분석 -> 명사,동사만 꺼내서 결과값 반환해주기
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
        // 일본어인지 확인하고 아니면 해당 인덱스를 기준으로 삭제
            if (preg_match('/[\p{Script=Hiragana}\p{Script=Katakana}\p{Script=Han}]/u', $sourceLine)) {
                $filteredSourceTextArray[] = $sourceLine;
                $filteredTargetTextArray[] = $targetTextArray[$index];
            }
        }

        // 일어와 번역된 한국어를 반환
        return response()->json(['sourceTextArray' => $filteredSourceTextArray, 'targetTextArray' => $filteredTargetTextArray]);
    }
}
