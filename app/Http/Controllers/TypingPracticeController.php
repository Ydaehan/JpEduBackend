<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sentence;

class TypingPracticeController extends Controller
{
    public function fileOpen(Request $request) {
        if($request->hasFile('file')){
            $file = $request->file('file');
            // 파일 열기
            $contents = file_get_contents($file->getRealPath());
            // 파일 읽기
            $replace_search = array("\n","\r");
            $replace_target = array("","");
            $contents = str_replace($replace_search, $replace_target, $contents);
            $contents = str_replace('。', "\n", $contents);
            // 줄바꿈 문자를 기준으로 분리하여 배열로 만듦
            $lines = explode("\n", $contents);
            // 배열의 각 원소를 순회하며 처리
            foreach ($lines as $line) {
                if($line != ''){
                    $existingSentence = Sentence::where('sentence', $line)->first();
                    //똑같은 문장이면 들어가지 않게 처리
                    if(!$existingSentence){
                        Sentence::create(['sentence' => $line]);
                    }
                }
            }
        } else {
            echo "파일이 없습니다.";
        }
    }
}
