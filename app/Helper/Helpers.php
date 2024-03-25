<?php

use App\Models\User;
use Carbon\Carbon;
use Youaoi\MeCab\MeCab;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client;

function getMecab($sourceArray)
{
  $mecab = new Mecab();
  $gana = [];
  $kanji = [];

  $i = 0;
  foreach ($sourceArray as $index => $item) {
    $reading = $mecab->parse($item); // mecab
    foreach ($reading as $value) {
      if ($value->reading != null) {
        if ($value->speech == '動詞' || $value->speech == '名詞' || $value->speech == '助動詞' || $value->speech == '形容詞') {
          $gana[$i] = $value->reading;
          $kanji[$i] = $value->original;
          $i++;
        }
      }
    }
  }

  $i = 0;
  foreach ($kanji as $item) {
    $resultReading = $mecab->parse($item);
    foreach ($resultReading as $value) {
      if ($value->reading != null) {
        if ($value->speech == '動詞' || $value->speech == '名詞' || $value->speech == '助動詞' || $value->speech == '形容詞') {
          $gana[$i] = $value->reading;
          $kanji[$i] = $value->original;
          $i++;
        }
      }
    }
  }

  // index 번호 제거
  $kanji = array_values($kanji);
  $gana = array_values($gana);
  foreach ($gana as $index => $text) {
    $gana[$index] = convertKatakanaToHiragana($text);
  }
  $result = [$kanji, $gana];
  return $result;
}

// 한자 필터링
function kanjiFilter($kanji)
{
  foreach ($kanji as $index => $item) {
    preg_match_all('!['
      . '\x{2E80}-\x{2EFF}' // 한,중,일 부수 보충
      . '\x{31C0}-\x{31EF}\x{3200}-\x{32FF}'
      . '\x{3400}-\x{4DBF}\x{4E00}-\x{9FBF}\x{F900}-\x{FAFF}'
      . '\x{20000}-\x{2A6DF}\x{2F800}-\x{2FA1F}' // 한,중,일 호환한자
      . ']+!u', $item, $match);
    // 히라가나와 가타카나를 제외한 한자만을 포함하는지 확인
    if (empty($match[0])) {
      $kanji[$index] = null;
    }
  }
  return $kanji;
}

// 가타카나 -> 히라가나 변환
function convertKatakanaToHiragana($text)
{
  return mb_convert_kana($text, 'c');
}

function getKanji($sourceTextArray)
{
  $filteredSourceTextArray = [];
  // $filteredTargetTextArray = [];
  foreach ($sourceTextArray as $sourceLine) {
    // 일본어인지 확인하고 한자 부분에 한글이 인식되었을 때 해당 인덱스를 기준으로 삭제
    // 해당 일본어에 한자가 포함되어 있으면 지우지 않기

    $filteredSourceLine = preg_replace('/[^\x{3040}-\x{309F}\x{30A0}-\x{30FF}\x{31F0}-\x{31FF}\x{2E80}-\x{2EFF}\x{31C0}-\x{31EF}\x{3200}-\x{32FF}\x{3400}-\x{4DBF}\x{4E00}-\x{9FBF}\x{F900}-\x{FAFF}\x{20000}-\x{2A6DF}\x{2F800}-\x{2FA1F}]+/u', '', $sourceLine);
    // 필터링된 문자열이 비어있지 않은 경우에만 추가
    if (!empty($filteredSourceLine)) {
      $filteredSourceTextArray[] = $filteredSourceLine;
    }
  }
  // $result = [$filteredSourceTextArray, $filteredTargetTextArray];
  $result = $filteredSourceTextArray;
  return $result;
}

function setImageSize($file)
{
  $ext = getimagesize($file->path());
  $originWidth = $ext[0];
  $originHeight = $ext[1];

  // 비율 수정
  // 최대 비율은 1960 * 1960
  if ($originWidth > 3000 || $originHeight > 3000) {
    $s = 0.4;
  } else if ($originWidth > 1960 || $originHeight > 1960) {
    $s = 0.7;
  } else {
    $s = 1;
  }

  $newWidth = $originWidth * $s;
  $newHeight = $originHeight * $s;

  switch ($ext['mime']) {
    case 'image/jpeg':
      $image = imagecreatefromjpeg($file->path());
      break;
    case 'image/png':
      $image = imagecreatefrompng($file->path());
      break;
  }

  // resize 대상 image 생성
  $reImage = imagecreatetruecolor($newWidth, $newHeight);
  imagecopyresampled($reImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $originWidth, $originHeight);

  // 새 이미지 파일로 저장
  $newImagePath = $file->path() . '_resized.jpg';
  imagejpeg($reImage, $newImagePath);

  return $newImagePath;
}

function duplicateCheck($kanji, $gana, $meaning)
{
  $unique = [];
  $resultKanji = [];
  $resultGana = [];
  $resultMeaning = [];

  for ($i = 0; $i < count($meaning); $i++) {
    $key = $kanji[$i] . '|' . $gana[$i] . '|' . $meaning[$i];

    if (!isset($unique[$key])) {
      $unique[$key] = true;
      $resultMeaning[] = $meaning[$i];
      $resultGana[] = $gana[$i];
      $resultKanji[] = $kanji[$i];
    }
  }

  return [
    $resultKanji, $resultGana, $resultMeaning
  ];
}

function dailyCheck()
{
  /** @var \App\Models\User $user **/
  $user = auth('sanctum')->user();
  $userSetting = $user->userSetting;

  $now = now();
  $today = $now->startOfDay();
  $yesterday = $now->copy()->subDay()->startOfDay();
  $lastCheck = $user->dailyChecks()->latest('checked_at')->first();


  if (!$lastCheck) {
    $user->dailyChecks()->create([
      'checked_at' => $today
    ]);
    $userSetting->streak = 1;
    $userSetting->save();
    return ['message' => 'daily check success', 'streak' => $userSetting->streak];
  }

  $lastCheckDate = Carbon::parse($lastCheck->checked_at);

  if ($lastCheckDate->eq($today)) {
    return ['message' => 'already checked today', 'streak' => $userSetting->streak];
  }

  $user->dailyChecks()->create([
    'checked_at' => $today
  ]);

  if ($lastCheckDate->eq($yesterday)) {
    $userSetting->streak += 1;
  } else {
    $userSetting->streak = 1;
  }
  $userSetting->save();
  return ['message' => 'daily check success', 'streak' => $userSetting->streak];
}

// 사용자 토큰을 생성, 응답을 반환하는 메서드
function createTokensAndRespond(User $user)
{
  dailyCheck(); // 출석 체크
  $user->tokens()->delete();
  $accessToken = $user->createToken('API Token', ['*'], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
  $refreshToken = $user->createToken('Refresh Token', ['*'], Carbon::now()->addMinutes(config('sanctum.rt_expiration')));
  return response()->json([
    'status' => 'Success',
    'user' => $user,
    'access_token' => $accessToken->plainTextToken,
    'refresh_token' => $refreshToken->plainTextToken,
  ], 200);
}

function papagoTranslation($text)
{
  $client_id = config('services.papago.client_id');
  $client_secret = config('services.papago.client_secret');
  $encText = urlencode($text);
  $postvars = "source=ja&target=ko&text=" . $encText;
  $url = "https://naveropenapi.apigw.ntruss.com/nmt/v1/translation";
  $is_post = true;
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_POST, $is_post);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $postvars);
  $headers = array();
  $headers[] = "X-NCP-APIGW-API-KEY-ID: " . $client_id;
  $headers[] = "X-NCP-APIGW-API-KEY: " . $client_secret;
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  $response = curl_exec($ch);
  $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  echo "status_code:" . $status_code . "<br />";
  curl_close($ch);
  $response = json_decode($response, true);
  if ($status_code == 200) {
    return $response['message']['result']['translatedText'];
  } else {
    return ['message' => 'failed'];
  }
}
