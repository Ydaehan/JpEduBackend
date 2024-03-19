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

  $sourceArray = array_values($sourceArray);
  $targetArray = array_values($targetArray);
  $gana = array_values($gana);
  $result = [$sourceArray, $gana, $targetArray];
  return $result;
}

function getKanji($sourceTextArray, $targetTextArray)
{
  $filteredSourceTextArray = [];
  $filteredTargetTextArray = [];
  foreach ($sourceTextArray as $index => $sourceLine) {
    if (preg_match_all('!['
      . '\x{2E80}-\x{2EFF}'
      . '\x{31C0}-\x{31EF}\x{3200}-\x{32FF}'
      . '\x{3400}-\x{4DBF}\x{4E00}-\x{9FBF}\x{F900}-\x{FAFF}'
      . '\x{20000}-\x{2A6DF}\x{2F800}-\x{2FA1F}'
      . ']+!u', $sourceLine, $match)) {
      $filteredSourceTextArray[] = $sourceLine;
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
