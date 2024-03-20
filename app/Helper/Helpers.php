<?php

use Youaoi\MeCab\MeCab;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\Utils;

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

// 파파고 번역 ja -> ko
function papagoTranslation($source_lang, $target_lang, $texts)
{
  $client_id = env('APP_NAVER_PAPAGO_TEXT_TRANSLATION_CLIENT_ID');
  $client_secret = env('APP_NAVER_PAPAGO_TEXT_TRANSLATION_CLIENT_SECRET_KEY');
  $client = new Client();
  $promises = [];

  foreach ($texts as $text) {
    $postvars = [
      "source" => $source_lang,
      "target" => $target_lang,
      "text" => $text,
    ];
    $headers = [
      "X-NCP-APIGW-API-KEY-ID" => $client_id,
      "X-NCP-APIGW-API-KEY" => $client_secret,
    ];
    $promises[] = $client->requestAsync('POST', 'https://naveropenapi.apigw.ntruss.com/nmt/v1/translation', [
      'headers' => $headers,
      'form_params' => $postvars,
    ]);
  }

  $results = Utils::settle($promises)->wait();

  $translations = [];
  foreach ($results as $result) {
    if ($result['state'] === 'fulfilled') {
      $response = $result['value'];
      $body = $response->getBody();
      $json = json_decode($body, true);
      $translations[] = $json['message']['result']['translatedText'];
    } else {
      echo 'Error: ', $result['reason']->getMessage(), "\n";
      $translations[] = null;
    }
  }

  return $translations;
}

function duplicateCheck($kanji, $gana, $meaning)
{
  $unique = [];
  $resultKanji = [];
  $resultGana = [];
  $resultMeaning = [];

  for ($i = 0; $i < count($meaning); $i++) {
    $key = $meaning[$i] . '|' . $gana[$i];

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

  if (!$lastCheck || !$lastCheck->checked_at->eq($today)) {
    $user->dailyChecks()->create([
      'checked_at' => $today
    ]);

    if ($lastCheck && $lastCheck->checked_at->eq($yesterday)) {
      $userSetting->streak += 1;
    } else {
      $userSetting->streak = 1;
    }
    $userSetting->save();
    return ['message' => 'daily check success', 'streak' => $userSetting->streak];
  }

  return ['message' => 'already checked today', 'streak' => $userSetting->streak];
}
