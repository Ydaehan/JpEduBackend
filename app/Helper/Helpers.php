<?php

use Youaoi\MeCab\MeCab;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

function getMecab($sourceArray, $targetArray)
{
  // sourceArray의 요소들을 형태소분석하여 동사와 명사만 추출 하여 읽는법 까지 3가지로 반환해주기
  $mecab = new Mecab();
  $gana = [];

  foreach ($sourceArray as $index => $item) {
    $reading = $mecab->parse($item);
    if ($reading[0]->speech === '動詞' || $reading[0]->speech === '名詞') {
      $gana[$index] = $reading[0]->reading;
    } else {
      unset($sourceArray[$index]); // unset 시 index 번호가 붙음
      unset($targetArray[$index]);
    }
  }

  // index 번호 제거
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
    // 일본어인지 확인하고 한자 부분에 히라가나만 있거나 한글이 인식되었을 때 해당 인덱스를 기준으로 삭제
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
  for ($i = 0; $i < count($meaning); $i++) {
    $isDuplicate = false;

    for ($j = 0; $j < $i; $j++) {
      if ($meaning[$i] === $meaning[$j] && $gana[$i] === $gana[$j]) {
        $isDuplicate = true;
        break;
      }
    }

    if (!$isDuplicate) {
      $resultMeaning[] = $meaning[$i];
      $resultGana[] = $gana[$i];
      $resultKanji[] = $kanji[$i];
    }
  }

  return [$resultKanji, $resultGana, $resultMeaning];
}

function dailyCheck()
{
  $user = auth('sanctum')->user();
  $now = now();
  $today = $now->startOfDay();
  $yesterday = $now->copy()->subDay()->startOfDay();
  $lastCheck = $user->dailyChecks()->latest('checked_at')->first();

  if (!$lastCheck) {
    $user->dailyChecks()->create([
      'checked_at' => $today
    ]);
    return ['message' => 'daily check success', 'streak' => 1];
  }

  if ($lastCheck->checked_at->eq($today)) {
    return ['message' => 'already checked today', 'streak' => $user->userSetting->streak];
  }

  if ($lastCheck->checked_at->eq($yesterday)) {
    $user->userSetting->streak += 1;
    $user->userSetting->save();
  } else {
    $user->userSetting->streak = 1;
    $user->userSetting->save();
  }

  $user->dailyChecks()->create([
    'checked_at' => $today
  ]);

  return ['message' => 'daily check success', 'streak' => $user->userSetting->streak];
}
