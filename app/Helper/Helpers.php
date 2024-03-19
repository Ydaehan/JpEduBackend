<?php

use Youaoi\MeCab\MeCab;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;

function getMecab($sourceArray, $targetArray)
{
  $mecab = new Mecab();
  $gana = [];

  foreach ($sourceArray as $index => $item) {
    $reading = $mecab->parse($item);
    if ($reading[0]->speech === '動詞' || $reading[0]->speech === '名詞') {
      $gana[$index] = $reading[0]->reading;
    } else {
      unset($sourceArray[$index]);
      unset($targetArray[$index]);
    }
  }

  foreach ($sourceArray as $index => $item) {
    preg_match_all('!['
      . '\x{2E80}-\x{2EFF}' // 한,중,일 부수 보충
      . '\x{31C0}-\x{31EF}\x{3200}-\x{32FF}'
      . '\x{3400}-\x{4DBF}\x{4E00}-\x{9FBF}\x{F900}-\x{FAFF}'
      . '\x{20000}-\x{2A6DF}\x{2F800}-\x{2FA1F}' // 한,중,일 호환한자
      . ']+!u', $item, $match);
    // 히라가나와 가타카나를 제외한 한자만을 포함하는지 확인
    if (empty($match[0])) {
      $sourceArray[$index] = null;
    }
  }

  // index 번호 제거
  $sourceArray = array_values($sourceArray);
  $targetArray = array_values($targetArray);
  $gana = array_values($gana);
  foreach ($gana as $index => $text) {
    $gana[$index] = convertKatakanaToHiragana($text);
  }
  $result = [$sourceArray, $gana, $targetArray];
  return $result;
}

// 가타카나 -> 히라가나 변환
function convertKatakanaToHiragana($text)
{
  return mb_convert_kana($text, 'c');
}

function getKanji($sourceTextArray, $targetTextArray)
{
  $filteredSourceTextArray = [];
  $filteredTargetTextArray = [];
  foreach ($sourceTextArray as $index => $sourceLine) {
    // 일본어인지 확인하고 한자 부분에 한글이 인식되었을 때 해당 인덱스를 기준으로 삭제
    // 히라가나 or 가타카나 일 경우 mecab 동작 이후 null값으로 변경
    // 해당 일본어에 한자가 포함되어 있으면 지우지 않기

    $filteredSourceLine = preg_replace('/[^\x{3040}-\x{309F}\x{30A0}-\x{30FF}\x{31F0}-\x{31FF}\x{2E80}-\x{2EFF}\x{31C0}-\x{31EF}\x{3200}-\x{32FF}\x{3400}-\x{4DBF}\x{4E00}-\x{9FBF}\x{F900}-\x{FAFF}\x{20000}-\x{2A6DF}\x{2F800}-\x{2FA1F}]+/u', '', $sourceLine);
    // 필터링된 문자열이 비어있지 않은 경우에만 추가
    if (!empty($filteredSourceLine)) {
      $filteredSourceTextArray[] = $filteredSourceLine;
      $filteredTargetTextArray[] = $targetTextArray[$index];
    }
  }
  $result = [$filteredSourceTextArray, $filteredTargetTextArray];
  return $result;
}

// function duplicateCheck($kanji, $gana, $meaning)
// {
//   for ($i = 0; $i < count($meaning); $i++) {
//     $isDuplicate = false;

//     for ($j = 0; $j < $i; $j++) {
//       if ($meaning[$i] === $meaning[$j] && $gana[$i] === $gana[$j]) {
//         $isDuplicate = true;
//         break;
//       }
//     }

//     if (!$isDuplicate) {
//       $resultMeaning[] = $meaning[$i];
//       $resultGana[] = $gana[$i];
//       $resultKanji[] = $kanji[$i];
//     }
//   }

//   return [$resultKanji, $resultGana, $resultMeaning];
// }

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
