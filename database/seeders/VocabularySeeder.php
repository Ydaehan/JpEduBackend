<?php

namespace Database\Seeders;

use App\Imports\VocabularyNoteImport;
use App\Models\User;
use App\Models\VocabularyNote;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;

class VocabularySeeder extends Seeder
{
  private function export()
  {
    $excelFiles = ['JLPT_Voca_N5', 'JLPT_Voca_N4', 'JLPT_Voca_N3'];
    foreach ($excelFiles as $file) {
      $path = storage_path('app/excelVocabulary/' . $file . '.xlsx');
      $vocabularyNote = new VocabularyNoteImport();
      Excel::import($vocabularyNote, $path);
      $excel = $vocabularyNote->getVocabularyNote();
      $duplicateResult = duplicateCheck($excel['kanji'], $excel['gana'], $excel['meaning']);
      list($kanji, $gana, $meaning) = $duplicateResult;
      $owner = User::where('id', rand(1, 2))->first();
      $setting = $owner->userSetting;
      switch ($file) {
        case 'JLPT_Voca_N5':
          $level = 5;
          break;
        case 'JLPT_Voca_N4':
          $level = 4;
          break;
        case 'JLPT_Voca_N3':
          $level = 3;
          break;
        case 'JLPT_Voca_N2':
          $level = 2;
          break;
        case 'JLPT_Voca_N1':
          $level = 1;
          break;
      };
      VocabularyNote::create([
        'title' => $file,
        'user_id' => $owner->id,
        'level_id' => $level,
        'kanji' => json_encode($kanji),
        'gana' => json_encode($gana),
        'meaning' => json_encode($meaning),
        'is_public' => true,
        'is_creator' => true
      ]);
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
