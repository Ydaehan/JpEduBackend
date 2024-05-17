<?php

namespace Database\Seeders;

use App\Imports\VocabularyNoteImport;
use App\Models\User;
use App\Models\VocabularyNote;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class VocabularySeeder extends Seeder
{
  private function export()
  {
    $excelFiles = getFilesFromS3('vocabulary-notes');
    foreach ($excelFiles as $fileName) {
      $file = Storage::disk('s3')->get($fileName);
      // 원본 파일의 확장자 가져오기
      $extension = pathinfo($fileName, PATHINFO_EXTENSION);
      // 임시 파일로 저장
      $tempPath = tempnam(sys_get_temp_dir(), 'excel') . '.' . $extension;
      file_put_contents($tempPath, $file);
      // 엑셀 파일 읽기
      $vocabularyNote = new VocabularyNoteImport();
      Excel::import($vocabularyNote, $tempPath);
      $excel = $vocabularyNote->getVocabularyNote();
      $duplicateResult = duplicateCheck($excel['kanji'], $excel['gana'], $excel['meaning']);
      list($kanji, $gana, $meaning) = $duplicateResult;
      $owner = User::where('id', rand(1, 2))->first();
      switch ($fileName) {
        case 'vocabulary-notes/JLPT_Voca_N5.xlsx':
          $level = 5;
          $name = 'N5 단어장';
          break;
        case 'vocabulary-notes/JLPT_Voca_N4.xlsx':
          $level = 4;
          $name = 'N4 단어장';
          break;
        case 'vocabulary-notes/JLPT_Voca_N3.xlsx':
          $level = 3;
          $name = 'N3 단어장';
          break;
        case 'vocabulary-notes/JLPT_Voca_N2.xlsx':
          $level = 2;
          $name = 'N2 단어장';
          break;
        case 'vocabulary-notes/JLPT_Voca_N1.xlsx':
          $level = 1;
          $name = 'N1 단어장';
          break;
      };
      VocabularyNote::create([
        'title' => $name,
        'user_id' => $owner->id,
        'level_id' => $level,
        'kanji' => json_encode($kanji),
        'gana' => json_encode($gana),
        'meaning' => json_encode($meaning),
        'is_public' => true,
        'is_creator' => true
      ]);
      unlink($tempPath);
    }
  }
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $this->export();
  }
}
