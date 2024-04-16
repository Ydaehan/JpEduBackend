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
    $file = getFilesFromS3('sentences');
    foreach ($file as $fileName) {
      // 원본 파일의 확장자 가져오기
      $extension = pathinfo($fileName, PATHINFO_EXTENSION);
      // 임시 파일로 저장
      $tempPath = tempnam(sys_get_temp_dir(), 'txt') . '.' . $extension;
      // S3에서 파일 내용 가져오기
      $fileContent = Storage::disk('s3')->get($fileName);
      // 파일 내용을 임시 파일에 저장
      file_put_contents($tempPath, $fileContent);
      $contents = file_get_contents($tempPath); // 파일의 내용을 가져옴
      // 줄바꿈 문자를 기준으로 분리하여 배열로 만듦
      $replace_search = array("\n", "\r");
      $replace_target = array("", "");
      $contents = str_replace($replace_search, $replace_target, $contents);
      $contents = str_replace('。', "\n", $contents);
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
    // 파일 읽기

  }

  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $this->getSentenceData();
  }
}
