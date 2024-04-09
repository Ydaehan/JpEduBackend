<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Sentence;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class SentenceSeeder extends Seeder
{
  private function getSentenceData()
  {
    $file = 'app/sentence/typingtest.txt';
    // 파일 열기
    $path = storage_path($file); // storage의 path값을 가져옴
    $contents = file_get_contents($path); // 파일의 내용을 가져옴
    // 파일 읽기
    $replace_search = array("\n", "\r");
    $replace_target = array("", "");
    $contents = str_replace($replace_search, $replace_target, $contents);
    $contents = str_replace('。', "\n", $contents);
    // 줄바꿈 문자를 기준으로 분리하여 배열로 만듦
    $lines = explode("\n", $contents);
    // 배열의 각 원소를 순회하며 처리
    foreach ($lines as $line) {
      if ($line != '') {
        $existingSentence = Sentence::where('sentence', $line)->first();
        //똑같은 문장이면 들어가지 않게 처리
        // 문장의 길이 제한을 두어야 할것 같음
        if (!$existingSentence && mb_strlen($line, 'utf-8') < 37) {
          $user = User::where('role', 'admin')->first();
          $user->sentences()->create(['sentence' => $line]);
        }
      }
    }
  }

  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $this->getSentenceData();
  }
}
