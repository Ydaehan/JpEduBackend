<?php

use Youaoi\MeCab\MeCab;
function getMecab($sourceArray, $targetArray) {
    // sourceArray의 요소들을 형태소분석하여 동사와 명사만 추출 하여 읽는법 까지 3가지로 반환해주기
    $mecab = new Mecab();
    $gana = [];

    foreach ($sourceArray as $index => $item) {
        $reading = $mecab->parse($item);
        if($reading[0]->speech === '動詞' || $reading[0]->speech === '名詞'){
            $gana[$index] = $reading[0]->reading;
        }
        else{
            unset($sourceArray[$index]); // unset 시 index 번호가 붙음
            unset($targetArray[$index]);
        }
    }

    // index 번호 제거
    $sourceArray = array_values($sourceArray);
    $targetArray = array_values($targetArray);
    $result = [$sourceArray, $gana, $targetArray];
    return $result;
}


function getKanji($sourceTextArray, $targetTextArray) {
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
    $result = [$filteredSourceTextArray, $filteredTargetTextArray];
    return $result;
}
