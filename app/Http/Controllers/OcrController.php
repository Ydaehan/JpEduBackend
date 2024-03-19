<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OcrController extends Controller
{
  public function textOcr(Request $request)
  {
    $client_secret = env("APP_NAVER_CLOVA_OCR_SECRET_KEY");
    $url = env("APP_NAVER_APIGW_INVOKE_URL");
    $image_file = $request->file('image');

    $params = [
      'version' => 'V2',
      'requestId' => uniqid(),
      'timestamp' => time(),
      'images' => [
        [
          'format' => "jpg",
          'name' => "demo"
        ]
      ]
    ];
    // JSON 인코딩
    $json = json_encode($params);

    $response = Http::withHeaders([
      'X-OCR-SECRET' => $client_secret
    ])->attach('file', file_get_contents($image_file), 'image.jpg')->post($url, [
      'message' => $json
    ]);

    $status_code = $response->status();
    if ($status_code == 200) {
      echo $response->body();
    } else {
      echo "ERROR: " . $response->body();
    }
  }
}
